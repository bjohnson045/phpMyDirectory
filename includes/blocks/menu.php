<?php
class Menu_Block extends Template_Block {
    function content() {
        $block_menu_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_menu.tpl');
        $block_menu_template->cache_id = ($this->PMDR->get('Session')->get('user_id') ? 'block_menu_logged_in' : 'block_menu_logged_out').$this->PMDR->getLanguage('languageid');
        $block_menu_template->expire = 900;
        if(!$block_menu_template->isCached()) {
            $block_menu_template->set('title',$this->PMDR->getLanguage('block_menu'));
            $block_menu_template->set('links',$this->PMDR->get('CustomLinks')->getMenuHTML(),false);
        }
        return $block_menu_template;
    }
}
?>