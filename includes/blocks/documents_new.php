<?php
class Documents_New_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_documents_new_number'));
        }
        if($limit) {
            $block_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_documents_new.tpl');
            $block_template->cache_id = 'block_documents_new'.$limit;
            $block_template->expire = 900;
            if(!$block_template->isCached()) {
                $results = $this->db->GetAll("SELECT d.id, d.listing_id, d.title, l.friendly_url, d.date, d.description, d.extension FROM ".T_DOCUMENTS." d INNER JOIN ".T_LISTINGS." l ON d.listing_id=l.id WHERE l.status='active' ORDER BY d.date DESC LIMIT ?",array($limit));
                if(is_array($results) AND sizeof($results) > 0) {
                    foreach($results as $key=>$value) {
                        $results[$key]['date'] = $this->PMDR->get('Dates_Local')->formatDate($value['date']);
                        $results[$key]['url'] = $this->PMDR->get('Listings')->getURL($value['listing_id'],$value['friendly_url'],'','/documents.html','listing_documents.php');
                    }
                }
                $block_template->set('results',$results);
            }
            return $block_template;
        }
    }
}
?>