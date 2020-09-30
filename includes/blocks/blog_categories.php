<?php
class Blog_Categories_Block extends Template_Block {
    function content() {
        if(intval($this->PMDR->getConfig('blog_block_number')) > 0 AND ADDON_BLOG) {
            $block_blog_categories_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_blog_categories.tpl');
            $block_blog_categories_template->cache_id = 'block_blog_categories';
            $block_blog_categories_template->expire = 900;
            if(!($block_blog_categories_template->isCached())) {
                $blog_categories = $this->db->GetAll("SELECT c.*, IFNULL(COUNT(bcl.blog_id),0) AS post_count FROM ".T_BLOG_CATEGORIES." c LEFT JOIN ".T_BLOG_CATEGORIES_LOOKUP." bcl ON c.id=bcl.category_id LEFT JOIN ".T_BLOG." b ON bcl.blog_id=b.id AND b.status='active' AND DATE(b.date_publish) <= CURDATE() GROUP BY c.id ORDER BY title ASC");
                foreach($blog_categories AS $key=>$blog_category) {
                    $blog_categories[$key]['url'] = $this->PMDR->Get('Blog')->getCategoryURL($blog_category['id'],$blog_category['friendly_url']);
                }
            }
            $block_blog_categories_template->set('blog_categories',$blog_categories);
            return $block_blog_categories_template;
        }
    }
}
?>