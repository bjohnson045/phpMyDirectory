<?php
class Breadcrumbs_Block extends Template_Block {
    function content() {
        $template_breadcrumbs = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_breadcrumbs.tpl');
        if($this->PMDR->get('breadcrumb')) {
            $template_breadcrumbs->set('breadcrumb',$this->PMDR->get('breadcrumb'));
        } else {
            $template_breadcrumbs->set('breadcrumb',false);
        }
        return $template_breadcrumbs;
    }
}
?>