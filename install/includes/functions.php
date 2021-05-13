<?php
function getURL() {
    if(array_key_exists('HTTPS',$_SERVER) AND strtolower($_SERVER['HTTPS']) == 'on') {
        $url = 'https';
    } else {
        $url = 'http';    
    }
    $url .= '://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'].(!in_array($_SERVER['SERVER_PORT'],array(80,443)) ? ':'.$_SERVER['SERVER_PORT'] : '')).urldecode(!empty($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] : $_SERVER['REQUEST_URI']);
    return $url;
}

function validPHPVersion($version) {
    return (version_compare(PHP_VERSION,$version,'>=')==1);
}

function validPermissions() {
    $bad = array();
    $folders_writable = array(
        '/files/banner',
        '/files/blog',
        '/files/categories',
        '/files/classifieds',
        '/files/classifieds/thumbnails',
        '/files/documents',
        '/files/images',
        '/files/images/thumbnails',
        '/files/locations',
        '/files/logo',
        '/files/logo/thumbnails',
        '/files/profiles',
        '/files/screenshots',
        '/files/site_links',
        '/files/temp',
        '/files/upload'
    );
    foreach ($folders_writable as $value) {
        if(!is_writable("..".$value)) {
            $bad[] = array('file'=>$value.'/','permission'=>'755 or 777');
        }
    }
    if(!is_writable('../defaults.php')) {
        $bad[] = array('file'=>'defaults.php','permission'=>'777');
    }

    clearstatcache();
    unset($folders);
    if(count($bad) < 1) {
        return -1;
    } else {
        return $bad;
    }
}

function validGD() {
    if(!get_extension_funcs("gd") OR !extension_loaded('gd')) {
        return false;
    }
    $gd_info = gd_info();
    return strstr($gd_info['GD Version'],"2.");
}

function validCURL() {
    return function_exists(curl_version);
}

function validMySQL( $main, $minor, $sub ) {
    $temp = $main*10000 + $minor*100 + $sub;
    return ($temp <= mysqli-get-server-version($mysql_conn));
}

function validModRewrite() {
    return in_array('mod_rewrite', apache_get_modules());
}

function validionCube($version = null) {
    if(function_exists('ioncube_loader_version')) {
        if(!is_null($version)) {
            return(version_compare(ioncube_loader_version(),$version,'>=')==1);
        } else {
            return true;
        }
    }
    if(function_exists('ioncube_loader_iversion')) {
        return true;
    }
    if(extension_loaded('ionCube Loader')) {
        return true;
    }
    return false;
}

function replaceVariables($string, $variables) {
    foreach($variables as $key=>$value) {
        $string = str_replace("{" . $key . "}",$value,$string);
    }
    return $string;
}

function buildDefaults($variables) {
    $template = @file_get_contents('./template/defaults.tpl');
    $defaults_content = str_replace(array("\r\n","\r"),array("\n"),replaceVariables($template, $variables));
    return $defaults_content;
}

function writeDefaults($content) {
    $file = '../defaults.php';

    if(!$handle = @fopen($file, 'w+')) {
        return false;
    }

    if(@fwrite($handle, $content) === FALSE) {
        return false;
    }

    fclose($handle);
    return true;
}

function importSQL($input_file, $variables = array()) {
    global $db;
    // read in SQL file
    $file_handle = @fopen($input_file,'r');
    $file = @fread($file_handle,800000);
    fclose($file_handle);

    $file = str_replace("\r\n","\n",$file);

    // remove any blank lines
    $lines = explode("\n",$file);
    foreach($lines as $key=>$value) {
        $value = trim($value);
        if($value != '') {
            $new_lines[] = $value;
        }
    }
    $lines = $new_lines;
    unset($new_lines);
    $file = implode("\n",$lines);

    // seperate file into one query per array element
    $lines = explode(";\n",$file);

    foreach($lines as $value) {
        $value = replaceVariables($value, $variables);
        $db->Execute($value);
    }
    return true;
}

function createTable($name, $structure, $character_set = '', $collation = '', $replace = false) {
    global $db;
    $sql = 'CREATE TABLE ';
    if(!$replace) {
        $sql .= 'IF NOT EXISTS ';
    }
    $sql .= $name.' (';
    foreach($structure['fields'] AS $field_name=>$field) {
        $sql .= '`'.$field_name.'` '.$field['type'];
        if($field['null'] == false) {
            $sql .= ' NOT';
        }
        $sql .= ' NULL';
        if(!empty($field['extra'])) {
            $sql .= ' '.$field['extra'];
        }
        if(!is_null($field['default'])) {
            $sql .= ' default \''.$field['default'].'\'';
        }
        $sql .= ',';
    }
    if(isset($structure['keys']) AND count($structure['keys'])) {
        foreach($structure['keys'] AS $key_name=>$key) {
            $sql .= $key['type'];
            if($key_name != 'PRIMARY') {
                $sql .= ' '.$key_name;
            }
            $sql .=' ('.implode(',',$key['fields']).'),';
        }
    }
    $sql = rtrim($sql,',');
    $sql .= ')';
    if(!empty($character_set)) {
        $sql .= ' CHARACTER SET '.$character_set;
    }
    if(!empty($collation)) {
        $sql .= ' COLLATE '.$collation;
    }
    $sql .= ' ENGINE='.$structure['engine'];
    $db->Execute($sql);
}

function installTables($prefix = '', $character_set = '', $collation = '') {
    $structure = include(PMDROOT.'/install/database/structure.php');
    foreach($structure AS $table_name=>$table) {
        createTable($prefix.$table_name,$table,$character_set,$collation);
    }
}

function loadData($table, $prefix, $fields=NULL, $fields_placeholders=NULL) {
    global $db;
    $data = include(PMDROOT.'/install/database/'.$table.'.php');
    $count = 0;
    foreach($data AS $data_part) {
        if(is_null($fields)) {
            $fields = array_keys($data_part);
            $fields_placeholders = implode(',',array_fill(0,count($fields),'?'));
            $fields = implode(',',array_keys($data_part));
        }
        $db->Execute("INSERT INTO ".$prefix.$table." (".$fields.") VALUES (".$fields_placeholders.")",$data_part);
    }
}

function loadPhrases($prefix) {
    global $db;
    $phrases = include(PMDROOT.'/install/database/language_phrases.php');
    foreach($phrases AS $section=>$phrase) {
        foreach($phrase AS $variable=>$content) {
            $db->Execute("INSERT INTO ".$prefix."language_phrases (section,variablename,content) VALUES (?,?,?)",array($section,$variable,$content));
        }
    }
}

function renamePhrase($old,$new) {
    global $db;
    $db->Execute("UPDATE ".T_LANGUAGE_PHRASES." SET variablename=? WHERE variablename=?",array($old,$new));
}

function arrayPHPFormat($data) {
    $data = var_export($data,true);
    $data = str_replace(' => ','=>',$data);
    $data = str_replace('array (','array(',$data);
    $data = preg_replace('/=>[\n\t\s]+array\(/','=>array(',$data);
    $data = str_replace('  ','    ',$data);
    return $data;
}
?>