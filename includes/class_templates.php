<?php
/**
* Templates Class
*/
class Templates extends TableGateway {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Database
    * @var Database
    */
    var $db;

    /**
    * Templates Constructor
    * @param object $PMDR
    * @return Templates
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_TEMPLATES;
    }

    /**
    * Get the URL of a file based on the child/parent template
    * @param string $file
    * @param string $url
    * @return string
    */
    function url($file,$url = BASE_URL) {
        if(defined('TEMPLATE_PATH_PARENT') AND !file_exists(PMDROOT.TEMPLATE_PATH.$file)) {
            return $url.TEMPLATE_PATH_PARENT.$file;
        } else {
            return $url.TEMPLATE_PATH.$file;
        }
    }

    /**
    * Get the CDN URL of the file based on child/template
    * @param string $file
    * @return string
    */
    function urlCDN($file) {
        return $this->url($file,CDN_URL);
    }

    /**
    * Get a template file path
    * @param string $file
    * @return boolean|string
    */
    function path($file) {
        if(file_exists(PMDROOT.TEMPLATE_PATH.$file)) {
            return PMDROOT.TEMPLATE_PATH.$file;
        } elseif(defined('TEMPLATE_PATH_PARENT') AND file_exists(PMDROOT.TEMPLATE_PATH_PARENT.$file)) {
            return PMDROOT.TEMPLATE_PATH_PARENT.$file;
        } else {
            return false;
        }
    }

    /**
    * Delete a template
    * @param int $id
    */
    function delete($id) {
        $this->deleteTemplateData($id);
        parent::delete($id);
    }

    /**
    * Delete template data stored in database
    * @param int $id
    */
    function deleteTemplateData($id) {
        $this->db->Execute("DELETE FROM ".T_TEMPLATES_DATA." WHERE template_id=?",array($id));
    }

    /**
    * Get all template files
    * @return array
    */
    function getAllFileTemplates() {
        $templates = array();
        if($handle = opendir(PMDROOT.'/template')) {
            $count = 0;
            while(false !== ($folder = readdir($handle))) {
                if(is_dir(PMDROOT.'/template/'.$folder) AND $folder != '.' AND $folder != '..') {
                    $templates[$count]['name'] = $folder;
                    $count++;
                }
            }
            closedir($handle);
        }
        return $templates;
    }

    /**
    * Get all available templates from database
    * @return array
    */
    function getCurrentTemplatesArray() {
        return $this->db->GetCol("SELECT folder FROM ".T_TEMPLATES);
    }

    /**
    * Sync a template between database and files
    * @param int $id
    */
    function sync($id) {
        $template = $this->getRow($id);
        $template_data = $this->PMDR->get('Templates_Data');
        $database_files = $this->db->GetAll("SELECT t.folder, td.name, td.subfolder, td.contents, UNIX_TIMESTAMP(td.date) AS date FROM ".T_TEMPLATES_DATA." td, ".T_TEMPLATES." t WHERE td.template_id = t.id AND t.id = ?",array($id));
        $file_archive = array();
        $failed = array();
        foreach($database_files AS &$file) {
            $file['path'] = PMDROOT.'/template/'.$file['folder'].($file['subfolder'] != '' ? '/'.$file['subfolder'] : '').'/'.$file['name'];
            $file_archive[] = $file['path'];
            if(!file_exists($file['path'])) {
                // We have to test absolute false because it could return 0 bytes
                if(file_put_contents($file['path'],$file['contents']) === false) {
                    $failed[] = $file['path'];
                }
            } elseif($file['date'] > filemtime($file['path'])) {
                // We have to test absolute false because it could return 0 bytes
                if(file_put_contents($file['path'],$file['contents']) === false) {
                    $failed[] = $file['path'];
                }
            } else {
                $this->db->Execute("UPDATE ".T_TEMPLATES_DATA." SET contents=?, date=NOW() WHERE name=? AND subfolder=? AND template_id=?",array(file_get_contents($file['path']),$file['name'],$file['subfolder'],$id));
            }
        }
        unset($file);

        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PMDROOT.'/template/'.$template['folder']));
        foreach($dir as $file) {
            $path_info = pathinfo($file);
            if(isset($path_info['extension']) AND ($path_info['extension'] == 'tpl' OR $path_info['extension'] == 'css')) {
                if(!in_array($file,$file_archive)) {
                    $subfolder = ltrim(str_replace(PMDROOT.'/template/'.$template['folder'],'',$path_info['dirname']),'/');
                    $this->db->Execute("INSERT INTO ".T_TEMPLATES_DATA." (template_id,subfolder,name,date,contents,contents_default) VALUES
                    (?,?,?,NOW(),?,?)",array($id,($subfolder ? $subfolder : ''),$path_info['basename'],file_get_contents($file),file_get_contents($file)));
                }
            }
        }
        unset($path_info,$dir,$file);

        return $failed;
    }

    /**
    * Import a template from a folder
    * @param string $folder
    */
    function importFromFolder($folder) {
         if(is_dir(PMDROOT.'/template/'.$folder)) {
            if(file_exists(PMDROOT.'/template/'.$folder.'/template.xml')) {
                $template_id = $this->insertFromXML(file_get_contents(PMDROOT.'/template/'.$folder.'/template.xml'),$folder);
            } else {
                $template_id = $this->insert(array('folder'=>$folder,'title'=>$folder,'description'=>'Unknown','author'=>'Unknown'));
            }
            $template_data = $this->PMDR->get('Templates_Data');
            $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PMDROOT.'/template/'.$folder), true);
            foreach($dir as $file) {
                $path_info = pathinfo($file);
                if($path_info['extension'] == 'tpl' OR $path_info['extension'] == 'css') {
                    $file_content = file_get_contents($file);
                    $subfolder = ltrim(str_replace(PMDROOT.'/template/'.$folder,'',$path_info['dirname']),'/');
                    $data = array(
                        'template_id'=>$template_id,
                        'subfolder'=>$subfolder ? $subfolder : '',
                        'name'=>$path_info['basename'],
                        'date'=>$this->PMDR->get('Dates')->dateTimeNow(),
                        'contents'=>$file_content,
                        'contents_default'=>$file_content
                    );
                    $template_data->insert($data);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
    * Insert a template from XML content
    * @param string $xml_content
    * @param string $folder
    */
    function insertFromXML($xml_content, $folder = '') {
        $xml = new SimpleXMLElement($xml_content);
        $data = array(
            'folder'=>$folder,
            'title'=>!empty($xml->template[0]->title) ? $xml->template[0]->title : 'Unknown',
            'description'=>!empty($xml->template[0]->description) ? $xml->template[0]->title : '',
            'author'=>!empty($xml->template[0]->author) ? $xml->template[0]->title : ''
        );
        return parent::insert($data);
    }
}
?>