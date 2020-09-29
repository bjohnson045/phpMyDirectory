<?php
/**
* Zones Class
*/
class Zones extends TableGateway {
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
    * @return Zones
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_ZONES;
    }

    /**
    * Get Feed
    * @param int $id Feed ID
    * @return array Zone
    */
    function getRow($id) {
        $zone = parent::getRow($id);
        $contents = $this->db->GetAll("SELECT type, type_id FROM ".T_ZONES_CONTENT." WHERE zone_id=?",array($id));
        foreach($contents AS $content) {
            $zone[$content['type']][] = $content['type_id'];
        }
        return $zone;
    }

    /**
    * Insert a zone
    * @param array $data
    * @return int Zone ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->updateZoneContent($data,$id);
        return $id;
    }

    /**
    * Update a zone
    * @param array $data
    * @param int $id
    * @return void
    */
    function update($data,$id) {
        $this->updateZoneContent($data,$id);
        parent::update($data,$id);
    }

    /**
    * Update Zone Content
    * @param array $data
    * @param int $id
    * @return void
    */
    function updateZoneContent($data,$id) {
        $this->db->Execute("DELETE FROM ".T_ZONES_CONTENT." WHERE zone_id=?",array($id));
        $content_types = $this->getContentTypes();
        foreach($content_types AS $type) {
            if(isset($data[$type])) {
                foreach($data[$type] AS $type_id) {
                    $this->db->Execute("INSERT INTO ".T_ZONES_CONTENT." (zone_id,type,type_id) VALUES (?,?,?)",array($id,$type,$type_id));
                }
            }
        }
    }

    /**
    * Get Content Types
    * @return array
    */
    function getContentTypes() {
        return array(
            'blocks'
        );
    }

    /**
    * Get Content Options
    * @return array
    */
    function getContentOptions() {
        $options = array();
        $option_types = $this->getContentTypes();
        foreach($option_types AS $type) {
            $function = 'get'.$type.'Options';
            if($type_options = $this->$function()) {
                $options[$type] = $type_options;
            }
        }
        return $options;
    }

    /**
    * Get Zone Content
    * @param mixed $zone Zone ID or variable
    * return string Content (usually HTML)
    */
    function getContent($zone) {
        $display_option = basename($_SERVER['SCRIPT_FILENAME'],'.php');
        if(!in_array($display_option,$this->getZoneDisplayOptions())) {
            return false;
        }
        if(is_numeric($zone)) {
            $zone_contents = $this->db->GetAll("SELECT zc.* FROM ".T_ZONES_CONTENT." zc INNER JOIN ".T_ZONES." z ON z.id=zc.zone_id WHERE z.id=? AND z.active=1 AND ".$this->db->CleanIdentifier($display_option)."=1",array($zone));
        } else {
            $zone_contents = $this->db->GetAll("SELECT zc.* FROM ".T_ZONES_CONTENT." zc INNER JOIN ".T_ZONES." z ON z.id=zc.zone_id WHERE z.variable=? AND z.active=1 AND ".$this->db->CleanIdentifier($display_option)."=1",array($zone));
        }
        if(!$zone_contents) {
            return false;
        }
        $content_merged = '';
        foreach($zone_contents AS $content) {
            $function = 'get'.$content['type'].'Content';
            if(method_exists($this,$function)) {
                $content_merged  .= $this->$function($content['type_id']);
            }
        }
        return $content_merged;
    }

    /**
    * Get the display option keys
    * @return array Display option keys
    */
    function getZoneDisplayOptions() {
        return array(
            'index',
            'listing',
            'classified',
            'browse_categories',
            'browse_locations',
            'search_results',
            'classifieds_search',
            'contact'
        );
    }

    /**
    * Get Block content
    * @param int $id Block ID
    * @param string $template_file Template file for custom display
    */
    function getBlocksContent($id) {
        if($block_content = $this->PMDR->get('Blocks')->getContent($id)) {
            return $block_content;
        }
        return false;
    }

    /**
    * Get Blocks content options
    * @return mixed Blocks array or false if no blocks
    */
    function getBlocksOptions() {
        if($blocks = $this->db->GetAssoc("SELECT id, title FROM ".T_BLOCKS)) {
            return $blocks;
        } else {
            return false;
        }
    }
}
?>