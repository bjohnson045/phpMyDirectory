<?php
class Blog_Posts_Block extends Template_Block {
    function content($limit = null) {
        if($this->PMDR->getConfig('blog_block_number') AND ADDON_BLOG) {
            $block_blog_posts_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_blog_posts.tpl');
            $block_blog_posts_template->cache_id = 'block_blog_posts';
            $block_blog_posts_template->expire = 900;
            if(!($block_blog_posts_template->isCached())) {
                if(is_null($limit)) {
                    $limit = $this->PMDR->getConfig('blog_block_number');
                }
                $blog_posts = $this->db->GetAll("SELECT * FROM ".T_BLOG." WHERE DATE(date_publish) <= CURDATE() AND status='active' ORDER BY date_publish DESC LIMIT ?",array(intval($limit)));
                foreach($blog_posts AS $key=>$blog_post) {
                    if(!empty($blog_post['content_short'])) {
                        $blog_posts[$key]['content'] = Strings::limit_words($blog_post['content_short'],$this->PMDR->getConfig('blog_block_characters'));
                    } elseif(strip_tags($blog_post['content']) != '') {
                        $blog_posts[$key]['content'] = Strings::limit_words(strip_tags($blog_post['content']),$this->PMDR->getConfig('blog_block_characters'));
                    }
                    $blog_posts[$key]['url'] = $this->PMDR->get('Blog')->getURL($blog_post['id'],$blog_post['friendly_url']);
                }
            }
            $block_blog_posts_template->set('blog_posts',$blog_posts);
            return $block_blog_posts_template;
        }
    }
}
?>