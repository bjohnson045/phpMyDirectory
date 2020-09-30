<?php
class Categories_Block extends Template_Block {
    function content() {
        if($this->PMDR->getConfig('block_categories_show')) {
            $block_categories_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_categories.tpl');
            if($location = $this->PMDR->get('active_location')) {
                $block_categories_template->cache_id = 'block_categories_location_'.$location['id'].$this->PMDR->getLanguage('languageid');
            } else {
                $block_categories_template->cache_id = 'block_categories'.$this->PMDR->getLanguage('languageid');
            }
            $block_categories_template->expire = 900;
            if(!($block_categories_template->isCached())) {
                $categories = $this->PMDR->get('Categories')->getRoots();
                foreach($categories as $key=>$category) {
                    if(($this->PMDR->getConfig('cat_empty_hidden') AND $category['count_total'] == 0) OR $category['hidden']) {
                        unset($categories[$key]);
                        continue;
                    }
                    if($category['link'] != '') {
                        $categories[$key]['url'] = $category['link'];
                    } elseif($location) {
                        $categories[$key]['url'] = $this->PMDR->get('Locations')->getURL($location['id'],$location['friendly_url_path'],$category['id'],$category['friendly_url_path']);
                    } else {
                        $categories[$key]['url'] = $this->PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
                    }
                }
            }
            $block_categories_template->set('categories',$categories);
            return $block_categories_template;
        }
    }
}
?>