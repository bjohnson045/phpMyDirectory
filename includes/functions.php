<?php
if(!defined('IN_PMD')) exit();

/**
* Print out an array or object
* Primary used for debugging purposes
* @param mixed $array
*/
function print_array($array,$exit = false) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
    if($exit) {
        exit();
    }
}

/**
* Check a string against the current file being viewed
* @param mixed $page String or array of strings to check
* @param bool $partial_match Allow partial matching
*/
function on_page($page,$partial_match = false) {
    if(!is_array($page)) {
        $page = array($page);
    }
    $current_page = substr($_SERVER['SCRIPT_FILENAME'],
        strpos($_SERVER['SCRIPT_FILENAME'], PMDROOT) + strlen(PMDROOT)
    );
    foreach($page AS $name) {
        if($partial_match) {
            if(strstr($current_page,$name)) {
                return true;
            }
        } elseif(ltrim($name,'/') == ltrim($current_page,'/')) {
            return true;
        }
    }
    return false;
}

/**
* Redirect based on specific parameters
* @param string $path Path to redirect to
* @param string $parameters Parameters to determine how the redirect is handled
*/
function redirect($path = null, $parameters = array()) {
    if(isset($parameters['from'])) {
        $parameters['from'] = urlencode_url($parameters['from']);
    }
    if(is_array($path)) {
        $parameters = $path;
        $path = null;
    }
    if(isset($_GET['from']) AND !empty($_GET['from']) AND !isset($parameters['from'])) {
        if($parameters === false) {
            $parameters = array('from'=>$_GET['from']);
            $path = urldecode($path);
        } else {
            $parameters = array();
            $path = base64_decode(urldecode($_GET['from']));
        }
    } elseif(!empty($path)) {
        $path = urldecode($path);
    } else {
        $path = URL_NOQUERY;
    }
    if(is_array($parameters) AND count($parameters) > 0) {
        $path .= '?';
        $path .= http_build_query($parameters);
    }
    redirect_url($path);
}

/**
* Redirect a URL
* @param string $path URL or path to redirect to
*/
function redirect_url($path) {
    session_write_close();
    header('Location: '.Strings::strip_new_lines($path));
    exit();
}

/**
* Redirect after an action
*/
function redirect_action() {
    redirect_url(rebuild_url(array(),array('id','action')));
}

/**
* Round up bytes to kilobytes, megabytes, etc
* @param string $size B|KB|MB|GB
* @return float Rounded size
*/
function round_up_bytes($size) {
    if($size < 1024) {
        return array('size'=>round($size,2),'label'=>'B');
    } elseif($size >= 1024 AND $size < 1048576) {
        return array('size'=>round(($size / 1024),2),'label'=>'KB');
    } elseif($size >= 1048576 AND $size < 1073741824) {
        return array('size'=>round(($size / 1048576),2),'label'=>'MB');
    } else {
        return array('size'=>round(($size / 1073741824),2),'label'=>'GB');
    }
}

/**
* Get the file extension using string methods
* @param string $file_name
* @return string
*/
function get_file_extension($file_name) {
    if(empty($file_name) OR !is_string($file_name)) {
        return false;
    }
    return strtolower(substr(strrchr($file_name,'.'), 1));
}

/**
* Get the file URL based on it's root path
* @param string $file
* @return string
*/
function get_file_url($file, $nocache = false) {
    if($file = find_file($file)) {
        $url = str_replace(FILES_PATH,FILES_URL,$file);
    } else {
        return false;
    }
    if($nocache) {
        $url .= '?revision='.md5_file($file);
    }
    return $url;
}

/**
* Get the file CDN URL based on it's root path
* @param string $file
* @return string
*/
function get_file_url_cdn($file) {
    // We only do the replacement if a CDN_URL has been set and is different than the BASE_URL
    // This allows a FILES URL to be used for static files while template files still remain in the local installation
    if(BASE_URL != CDN_URL) {
        return str_replace(FILES_URL,CDN_URL,get_file_url($file));
    } else {
        return get_file_url($file);
    }
}

/**
* Find a file while allowing * wildcard
* @param string $file
* @return mized
*/
function find_file($file, $all = false) {
    if(strstr($file,'*')) {
    if($results = glob($file)) {
            if($all AND count($results) > 1) {
                return $results;
            } elseif($file = array_shift($results)) {
            return $file;
        } else {
            return false;
        }
    } else {
        return false;
    }
    } elseif(file_exists($file)) {
        return $file;
    } else {
        return false;
    }
}

/**
* Unshift an element off an associative array
* @param array $arr Array to unshift from
* @param string $key Key to unshift
* @param mixed $val Value to unshift
* @return int Number of items on array
*/
function array_unshift_assoc(&$arr, $key, $val) {
    $arr = array_reverse($arr, true);
    $arr[$key] = $val;
    $arr = array_reverse($arr, true);
    return count($arr);
}

/**
* Get file format of an uploaded file
* We need this in case finfo or mime_content_type is not available so we can check the extension
* @param string $file
* @return string Mime type or format
*/
function get_uploaded_file_format($file) {
    if(!$format = get_file_format($file['tmp_name'])) {
        $format = get_file_format($file['name']);
    }
    return $format;
}

/**
* Get file format by checking MIME information
* @param string $file
* @return string Mime type or format
*/
function get_file_format($file) {
    if(function_exists('finfo_open')) {
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file);
        finfo_close($file_info);
        if($mime_type) {
            return $mime_type;
        }
    }

    if(function_exists('mime_content_type')) {
        if($mime_type = @mime_content_type($file)) {
            return $mime_type;
        }
    }

    if($extension = get_file_extension($file)) {
        switch($extension) {
            case 'js' :
                return 'application/x-javascript';
            case 'json' :
                return 'application/json';
            case 'jpg' :
            case 'jpeg' :
            case 'jpe' :
                return 'image/jpg';
            case 'png' :
            case 'gif' :
            case 'bmp' :
            case 'tiff' :
                return 'image/'.$extension;
            case 'css' :
                return 'text/css';
            case 'xml' :
                return 'application/xml';
            case 'doc' :
            case 'docx' :
                return 'application/msword';
            case 'xls' :
            case 'xlt' :
            case 'xlm' :
            case 'xld' :
            case 'xla' :
            case 'xlc' :
            case 'xlw' :
            case 'xll' :
                return 'application/vnd.ms-excel';
            case 'ppt' :
            case 'pps' :
                return 'application/vnd.ms-powerpoint';
            case 'rtf' :
                return 'application/rtf';
            case 'pdf' :
                return 'application/pdf';
            case 'html' :
            case 'htm' :
            case 'php' :
                return 'text/html';
            case 'txt' :
                return 'text/plain';
            case 'mpeg' :
            case 'mpg' :
            case 'mpe' :
                return 'video/mpeg';
            case 'mp3' :
                return 'audio/mpeg3';
            case 'wav' :
                return 'audio/wav';
            case 'aiff' :
            case 'aif' :
                return 'audio/aiff';
            case 'avi' :
                return 'video/msvideo';
            case 'wmv' :
                return 'video/x-ms-wmv';
            case 'mov' :
                return 'video/quicktime';
            case 'zip' :
                return 'application/zip';
            case 'tar' :
                return 'application/x-tar';
            case 'swf' :
                return 'application/x-shockwave-flash';
            default:
                return false;
        }
    }
    return false;
}

/**
* Standardize a URL by ensuring a protocol prefix
* @param string $url URL to standardize
* @return string Standardized URL
*/
function standardize_url($url) {
    if(empty($url)) return $url;
    return (strstr($url,'http://') OR strstr($url,'https://')) ? $url : 'http://'.$url;
}

/**
* Rebuild a URL by removing paramters or adding them
* @param mixed $add Parameters to add
* @param mixed $remove Parameters to remove
* @param mixed $open_ended Leave URL open with a ? character
* @return string Rebuilt URL
*/
function rebuild_url($add = array(), $remove = array(), $open_ended = false, $path = null) {
    $url_parts = parse_url(URL);
    if(!empty($url_parts['query'])) {
        parse_str($url_parts['query'],$query_string);
        foreach($remove AS $key) {
            unset($query_string[$key]);
        }
    } else {
        $query_string = array();
    }
    $query_string = array_merge($query_string,$add);
    $replace = '';
    if(count($query_string)) {
        $replace = '?'.http_build_query($query_string);
        if($open_ended) {
            $replace .= '&';
        }
    } elseif($open_ended) {
        $replace .= '?';
    }
    if(!is_null($path)) {
        return $path.$replace;
    } else {
        return URL_NOQUERY.$replace;
    }
}

/**
* Get IP Address
* @return string IP address
*/
function get_ip_address() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
* Unlink file
* @param string $file
*/
function unlink_file($file) {
    if(file_exists($file)) {
        return unlink($file);
    } else {
        return false;
    }
}

/**
* Unlink all files in a folder
* @param string $dir Folder to unlink files in
*/
function unlink_files($dir,$unlink_directories=false,$unlink_base=false,$exclude=array()) {
    if(!$dh = @opendir($dir)) {
        return;
    }
    while (false !== ($file = readdir($dh))) {
        if($file != '.' AND $file != '..' AND !in_array($file,$exclude)) {
            if(is_dir($dir.$file)) {
                if($unlink_directories) {
                    unlink_files($dir.$file.'/',$unlink_directories,$unlink_base,$exclude);
                }
            } else {
            @unlink($dir.$file);
        }
    }
    }
    closedir($dh);
    if($unlink_base) {
        rmdir($dir);
    }
    return true;
}

/**
* Unlink folder and all contents
* @param string $folder Folder to unlink
* @return bool true|false Success or failure
*/
function unlink_directory($folder)  {
    $folder = rtrim($folder,'/').'/';
    if(is_dir($folder)) {
        $dh = opendir($folder);
    }
    if(!$dh) return false;
    while(false !== ($file = readdir($dh))) {
        if($file != '.' AND $file != '..') {
            if(!is_dir($folder.$file)) {
                unlink($folder.$file);
            } else {
                unlink_directory($folder.$file.'/');
            }
        }
    }
    closedir($dh);
    rmdir($folder);
    return true;
}

/**
* Check if a specific URL exists
* @param string $url
* @return bool
*/
function url_exists($url) {
    if(@file_get_contents($url,0,NULL,0,1)) {
        return true;
    } else {
        return false;
    }
}

function move_files($source, $destination) {
    if(!file_exists($destination) AND !mkdir($destination)) {
        return false;
    }
    if(!is_dir($destination) OR !is_writable($destination)) {
        return false;
    }
    if($handle = opendir($source)) {
        while(false !== ($file = readdir($handle))) {
            if(is_file($source.'/'.$file)) {
                rename($source.'/'.$file, $destination.'/'.$file);
            }
        }
        closedir($handle);
    } else {
        return false;
    }
}

/**
* Parse a string as PHP
* @param string $string
* @return string
*/
function parse_php($string) {
    ob_start();
    eval('?>'.$string);
    $string = ob_get_contents();
    ob_end_clean();
    return $string;
}

/**
* Get an array of countries
* @return array Array of countries
*/
function get_countries_array() {
    include(PMDROOT.'/includes/country_codes.php');
    $countries = array();
    foreach($country_codes AS $code=>$country) {
        $countries[$country] = $country;
    }
    unset($country_codes,$code,$country);
    return $countries;
}

/**
* Get an array of countries
* @return array Array of countries
*/
function get_states_array() {
    include(PMDROOT.'/includes/state_codes.php');
    $states = array();
    foreach($state_codes AS $code=>$state) {
        $state = ucwords(strtolower($state));
        $states[$state] = $state;
    }
    unset($state_codes,$code,$state);
    return $states;
}

/**
* Check if a URL is valid
* @param string $url URL to check
* @param bool $ftp Allow FTP protocol
* @return bool
*/
function valid_url($url, $ftp = false) {
    if(!is_string($url)) {
        return false;
    }
    return preg_match('/('.($ftp ? 'ftp|' : '').'http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/',$url);
}

/**
* Base 64 encode a URL then urlencode it
* @param string $string URL to encode
* @return string Encoded URL
*/
function urlencode_url($string) {
    if(valid_url($string)) {
        return urlencode(base64_encode($string));
    } else {
        return $string;
    }
}

/**
* Get the value of an array key
* @param mixed $value
* @param string $key
* @param mized $default_value
* @return mixed
*/
function value($value,$key=0,$default=false) {
    if((is_string($key) OR is_int($key)) AND is_array($value)) {
        if(array_key_exists($key,$value)) {
            if(is_array($default) AND !is_array($value[$key])) {
                return array();
            }
            return $value[$key];
        }
    }
    return $default;
}

/**
* Get the first non empty value
*/
function coalesce() {
    $args = func_get_args();
    foreach($args as $arg) {
        if(!empty($arg)) {
            return $arg;
        }
    }
    return null;
}

/**
* Check if the path is within the root folder
* @param string $path
* @return bool true if path is valid
*/
function is_valid_path($path) {
    return strstr(realpath(dirname($path)),realpath(PMDROOT));
}
?>