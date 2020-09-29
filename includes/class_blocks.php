<?php
/**
* Blocks Class
*/
class Blocks extends TableGateway {
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
        $this->table = T_BLOCKS;
    }

    /**
    * Get Block
    * @param int $id Block ID
    * @return array Block
    */
    function getRow($id) {
        $block = parent::getRow($id);
        $data = $this->getData($id);
        $block = array_merge($block,$data);
        return $block;
    }

    /**
    * Get Block data
    * @param int $id Feed ID
    * @return array Zone
    */
    function getContent($id,$arguments=array()) {
        $block_class_name = ucfirst($id).'_Block';
        if(!class_exists($block_class_name) AND file_exists(PMDROOT.'/includes/blocks/'.$args[0].'.php')) {
            require_once(PMDROOT.'/includes/blocks/'.$args[0].'.php');
        }
        if(class_exists($block_class_name)) {
            $block = new $block_class_name($this->PMDR);
            $content = call_user_func_array(array($block, "content"),$arguments);
            return (is_object($content) AND get_class($content) == 'PMDTemplate') ? $content->render() : $content;
        } elseif($block = $this->db->GetRow("SELECT * FROM ".T_BLOCKS." WHERE (id=? OR variable=?) AND active=1",array($id,$id))) {
            if($block['cache_minutes'] == 0 OR is_null($block_content = $this->PMDR->get('Cache')->get('blocks'.$block['id'], intval($block['cache_minutes']), 'blocks'))) {
                $block_content = '';
                $data = $this->getData($block['id']);
                $block = array_merge($block,$data);
                switch($block['type']) {
                    case 'url_title_multiple':
                        if(empty($block['template']) OR !file_exists(PMDROOT.$block['template'])) {
                            $template_file = TEMPLATE_PATH.'blocks/url_title_list.tpl';
                        } else {
                            $template_file = $block['template'];
                        }
                        $urls = array();
                        $lines = explode("\n",$block['data_content']);
                        foreach($lines AS $line) {
                            $line_parts = explode('|',$line);
                            $urls[] = array('url_title'=>$line_parts[0],'url'=>$line_parts[1]);
                        }
                        $block_content = $this->PMDR->getNew('Template',PMDROOT.$template_file);
                        $block_content->set('urls',$urls);
                        $block_content = $block_content->render();
                        break;
                    case 'htmleditor':
                        $block_content = $block['data_content'];
                        break;
                    case 'rss':
                        $rss = $this->PMDR->get('RSS_Parser');
                        $rss->parse($block['data_url']);
                        if($rss->hasData()) {
                            if($block['data_limit'] > 0) {
                                $rss_items = $rss->getItems(0,$block['data_limit']);
                            } else {
                                $rss_items = $rss->getItems();
                            }
                            if(empty($block['template']) OR !file_exists(PMDROOT.$block['template'])) {
                                $template_file = TEMPLATE_PATH.'blocks/rss_feed.tpl';
                            } else {
                                $template_file = $block['template'];
                            }
                            $feed_content = $this->PMDR->getNew('Template',PMDROOT.$template_file);
                            $feed_content->set('items',$rss_items);
                            $block_content = $feed_content->render();
                        }
                }
                $this->PMDR->get('Cache')->write('blocks'.$block['id'],$block_content,'blocks');
            }
            return $block_content;
        } else {
            return false;
        }
    }

    /**
    * Get block meta data
    *
    * @param int $id Block ID
    * @return array Block data
    */
    function getData($id) {
        return $this->db->GetAssoc("SELECT CONCAT('data_',data_name), data_value FROM ".T_BLOCKS_DATA." WHERE block_id=?",array($id));
    }

    /**
    * Insert a zone
    * @param array $data
    * @return int Zone ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->updateData($data,$id);
        return $id;
    }

    /**
    * Update a zone
    * @param array $data
    * @param int $id
    * @return void
    */
    function update($data,$id) {
        parent::update($data,$id);
        $this->updateData($data,$id);
        $this->PMDR->get('Cache')->delete('blocks'.$id,'blocks');
    }

    /**
    * Delete a block
    *
    * @param int $id Block ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_BLOCKS_DATA." WHERE block_id=?",array($id));
        $this->PMDR->get('Cache')->delete('blocks'.$id,'blocks');
        parent::delete($id);
        return true;
    }

    /**
    * Update block data
    *
    * @param arra $data Array of block data
    * @param int $id Block ID
    */
    function updateData($data,$id) {
        foreach($data AS $key=>$value) {
            if(($position = strpos($key, 'data_')) !== FALSE) {
                $key = substr($key, $position+5);
                $this->db->Execute("INSERT INTO ".T_BLOCKS_DATA." (block_id, data_name, data_value) VALUES (?,?,?)",array($id, $key,$value));
            }
        }
    }

    /**
    * Update Zone Content
    * @param array $data
    * @param int $id
    * @return void
    */
    function getTypes() {
        return array(
            'url_title_multiple',
            'htmleditor',
            'rss',
        );
    }
}
?>