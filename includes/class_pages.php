<?php
/**
* Class CustomPage
* Create custom pages for display in site template
*/
class CustomPage extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * CustomPage Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->table = T_PAGES;
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
    }

    /**
    * Update a page
    *
    * @param array $data Page Data
    * @param int $id Page ID
    * @return void
    */
    function update($data,$id) {
        $this->deleteCache($id);
        parent::update($data,$id);
    }

    /**
    * Delete a page
    *
    * @param int $id Page ID
    * @return void
    */
    function delete($id) {
        parent::delete($id);
        $this->deleteCache($id);
    }

    /**
    * Delete the cache for a page
    * @param int $id Page ID
    */
    function deleteCache($id) {
        $this->PMDR->get('Cache')->delete('page_'.$id,'pages_');
    }

    /**
    * Get page row with formatted friendly url
    * @param integer $id Page ID
    * @return array Page data array
    */
    function getRow($id) {
        if(!$content_cached = $this->PMDR->get('Cache')->get('page_'.$id, 1800, 'pages_')) {
            if(MOD_REWRITE AND !is_numeric($id)) {
                $page = $this->db->GetRow("SELECT *,CONCAT('".BASE_URL_NOSSL."/pages/',friendly_url,'.html') as friendly_url_full FROM ".T_PAGES." WHERE friendly_url=?",array($id));
            } else {
                $page = $this->db->GetRow("SELECT *,CONCAT('".BASE_URL_NOSSL."/page.php?id=',id) as friendly_url_full FROM ".T_PAGES." WHERE id=".$this->PMDR->get('Cleaner')->clean_db($id));
            }
            if($page) {
                $page['content_parsed'] = $this->parseContent($page['content']);
                $this->PMDR->get('Cache')->write('page_'.$id,$page,'pages_');
            }
        } else {
            $page = $content_cached;
        }
        return $page;
    }

    /**
    * Get pages with formatted friendly url
    * @param integer $offset Offset for select
    * @param integer $limit Limit results
    * @return array Page data array
    */
    function getRows($condition = array(), $order = NULL, $offset = NULL, $count = NULL, $return_type ='all') {
        if(MOD_REWRITE) {
            $pages = $this->db->GetAll("SELECT *,CONCAT('".BASE_URL_NOSSL."/pages/',friendly_url,'.html') as friendly_url_full FROM ".T_PAGES." ORDER BY title ASC");
        } else {
            $pages = $this->db->GetAll("SELECT *,CONCAT('".BASE_URL_NOSSL."/page.php?id=',id) as friendly_url_full FROM ".T_PAGES." ORDER BY title ASC");
        }
        // Content is not parsed here as it can create memory issues
        return $pages;
    }

    /**
    * Parse the content of a page for any special content
    * @param string $content Original content
    * @return string Parsed content
    */
    function parseContent($content) {
        if(preg_match_all('/{external_feed_(\d+)}/',$content,$matches)) {
            $feeds = $this->PMDR->get('TableGateway',T_FEEDS_EXTERNAL);
            $rss = $this->PMDR->get('RSS_Parser');

            foreach($matches[1] AS $match) {
                $feed = $feeds->getRow(array('id'=>$match));
                $rss->parse($feed['url']);
                $replace_with = '';
                if($rss->hasData()) {
                    $rss_items = $rss->getItems();
                    $feed_content = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/rss_feed.tpl');
                    $feed_content->set('items',$rss_items);
                    $replace_with = $feed_content->render();
                }
                $content = str_replace('{external_feed_'.$match.'}',$replace_with,$content);
            }
        }
        return $content;
    }
}
?>