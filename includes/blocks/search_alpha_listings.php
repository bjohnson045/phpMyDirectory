<?php
class Search_Alpha_Listings_Block extends Template_Block {
    function content() {
        $template_search_alpha_links = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_search_alpha_listings.tpl');
        $template_search_alpha_links->cache_id = 'search_alpha_listings';
        $template_search_alpha_links->expire = 3600;
        $template_search_alpha_links->set('alpha_letters',explode('#',$this->PMDR->getConfig('alpha_index_search')));
        return $template_search_alpha_links;
    }
}
?>