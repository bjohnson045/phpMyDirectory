<?php
class Search_Alpha_Locations_Block extends Template_Block {
    function content($id = null) {
        $template_search_alpha_links = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_search_alpha_locations.tpl');
        $template_search_alpha_links->cache_id = 'search_alpha_locations';
        $template_search_alpha_links->expire = 3600;
        $template_search_alpha_links->set('title',$this->PMDR->getLanguage('block_search_alpha_locations'));
        $template_search_alpha_links->set('id',$id);
        $template_search_alpha_links->set('alpha_letters',explode('#',$this->PMDR->getConfig('alpha_index_search')));
        return $template_search_alpha_links;
    }
}
?>