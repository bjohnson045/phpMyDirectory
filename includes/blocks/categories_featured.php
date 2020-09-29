<?php
class Categories_Featured_Block extends Template_Block {
    function content() {
        if($this->PMDR->getConfig('block_categories_show')) {
            $block_categories_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_categories_featured.tpl');
            $block_categories_template->cache_id = 'block_categories_featured'.$this->PMDR->getLanguage('languageid');
            $block_categories_template->expire = 900;
            if(!($block_categories_template->isCached())) {
                $categories = $this->PMDR->get('Categories')->getFeatured();
                foreach($categories as $key=>$category) {
                    if(($this->PMDR->getConfig('cat_empty_hidden') AND $category['count_total'] == 0) OR $category['hidden']) {
                        unset($categories[$key]);
                        continue;
                    }
                    if($category['link'] != '') {
                        $categories[$key]['url'] = $category['link'];
                    } else {
                        $categories[$key]['url'] = $this->PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
                    }
                }
            }
            $block_categories_template->set('show_indexes',$this->PMDR->getConfig('show_indexes'));
            $block_categories_template->set('categories',$categories);
            return $block_categories_template;
        }
    }
}
?>