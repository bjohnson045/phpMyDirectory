<?php
class Social_Links_Block extends Template_Block {
    function content() {
        if(!$this->PMDR->getConfig('follow_links')) {
            return '';
        }
        $template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_social_links.tpl');
        $template->cache_id = 'block_social_links';
        $template->expire = 900;
        return $template;
    }
}
?>