<?php
class Classifieds_Featured_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_classifieds_featured_number'));
        }
        if($limit) {
            $block_classifieds_featured_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_classifieds_featured.tpl');
            $block_classifieds_featured_template->cache_id = 'block_classifieds_featured'.$limit;
            $block_classifieds_featured_template->expire = 900;
            $category_sql = '';
            $location_sql = '';
            if(($this->PMDR->getConfig('block_classifieds_featured_filter') == 'category' OR $this->PMDR->getConfig('block_classifieds_featured_filter') == 'category_location') AND $category = $this->PMDR->get('active_category')) {
                $category_sql = ' AND l.primary_category_id='.$category['id'];
                $block_classifieds_featured_template->cache_id .= '_category_'.$category['id'];
            }
            if(($this->PMDR->getConfig('block_classifieds_featured_filter') == 'location' OR $this->PMDR->getConfig('block_classifieds_featured_filter') == 'category_location') AND $location = $this->PMDR->get('active_location')) {
                $location_sql = ' AND l.location_id='.$location['id'];
                $block_classifieds_featured_template->cache_id .= '_location_'.$location['id'];
            }
            if(!($block_classifieds_featured_template->isCached())) {
                $featured_classifieds = $this->PMDR->get('Classifieds')->getFeatured($limit,$category_sql,$location_sql);
                $block_classifieds_featured_template->set('featured_classifieds',$featured_classifieds);
                $block_classifieds_featured_template->set('title',$this->PMDR->getLanguage('block_classifieds_featured'));
            }
            return $block_classifieds_featured_template;
        }
    }
}
?>