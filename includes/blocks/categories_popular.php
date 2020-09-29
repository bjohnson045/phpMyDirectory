<?php
class Categories_Popular_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_categories_popular_number'));
        }
        if($limit) {
            $block_categories_popular_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_categories_popular.tpl');
            $block_categories_popular_template->cache_id = 'block_category_popular'.$this->PMDR->getLanguage('languageid');
            $block_categories_popular_template->expire = 900;
            if(!$block_categories_popular_template->isCached()) {
                $categories_popular = $this->PMDR->get('Categories')->getPopular($limit);
                $categories_popular_array = array();
                $count = 0;
                foreach($categories_popular as $key=>$category) {
                    if(($this->PMDR->getConfig('cat_empty_hidden') AND $category['count_total'] == 0) OR $category['hidden']) {
                        unset($categories_popular[$key]);
                        continue;
                    }

                    if($count++ == $limit) break;
                    $categories_popular_array[$key] = $category;
                    $categories_popular_array[$key]['url'] = $this->PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
                }

                $block_categories_popular_template->set('categories',$categories_popular_array);
            }
            return $block_categories_popular_template;
        }
    }
}
?>