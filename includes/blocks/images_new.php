<?php
class Images_New_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_images_new_number'));
        }
        if($limit) {
            $block_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_images_new.tpl');
            $block_template->cache_id = 'block_images_new'.$limit;
            $block_template->expire = 900;
            if(!$block_template->isCached()) {
                $results = $this->db->GetAll("SELECT i.id, i.listing_id, i.title, l.friendly_url, i.date, i.extension FROM ".T_IMAGES." i INNER JOIN ".T_LISTINGS." l ON i.listing_id=l.id WHERE l.status='active' ORDER BY i.date DESC LIMIT ?",array(intval($limit)));
                if(is_array($results) AND sizeof($results) > 0) {
                    foreach($results as $key=>$value) {
                        $results[$key]['date'] = $this->PMDR->get('Dates_Local')->formatDate($value['date']);
                        $results[$key]['url'] = $this->PMDR->get('Listings')->getURL($value['listing_id'],$value['friendly_url'],'','/images.html','listing_images.php');
                        $results[$key]['image_url'] = get_file_url_cdn(IMAGES_PATH.$value['id'].'.*');
                        $results[$key]['image_thumb_url'] = get_file_url_cdn(IMAGES_THUMBNAILS_PATH.$value['id'].'.*');
                    }
                }
                $block_template->set('results',$results);
            }
            return $block_template;
        }
    }
}
?>