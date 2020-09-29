<?php
/**
 * Blog Class
 */
class blog extends TableGateway {
    /**
    * Registry object
    * @var object
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;

    /**
    * Blog constructor
    * @param object $PMDR
    * @return blog
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_BLOG;
    }

    /**
    * Get blog URL based on ID and friendly URL
    * @param int $id
    * @param string $friendly_url
    */
    function getURL($id, $friendly_url) {
        if(MOD_REWRITE) {
            return BASE_URL.'/'.$this->PMDR->getConfig('blog_url_path').'/'.$friendly_url.'-'.$id.'.html';
        } else {
            return BASE_URL.'/blog_post.php?id='.$id;
        }
    }

    /**
    * Get blog category URL based on ID and friendly URL
    * @param int $id
    * @param string $friendly_url
    */
    function getCategoryURL($id, $friendly_url) {
        if(MOD_REWRITE) {
            return BASE_URL.'/'.$this->PMDR->getConfig('blog_url_path').'/category/'.$friendly_url.'-'.$id.'.html';
        } else {
            return BASE_URL.'/blog.php?category_id='.$id;
        }
    }

    /**
    * Get the listing blog posts URL
    * @param int $listing_id
    * @param string $listing_friendly_url
    */
    function getListingPostsURL($listing_id,$listing_friendly_url) {
        if(MOD_REWRITE) {
            return BASE_URL.'/'.$listing_friendly_url.'/blog.html';
        } else {
            return BASE_URL.'/blog.php?listing_id='.$listing_id;
        }
    }

    /**
    * Send followers email based on comment id
    * @param int $comment_id
    * @return boolean true|false If sent
    */
    function sendFollowersEmail($comment_id) {
        $comment = $this->db->GetRow("SELECT * FROM ".T_BLOG_COMMENTS." WHERE id=?",array($comment_id));
        $blog_post = $this->db->GetRow("SELECT * FROM ".T_BLOG." WHERE id=?",array($comment['blog_id']));
        $comment['blog_post_url'] = $this->getURL($blog_post['id'],$blog_post['friendly_url']);
        $comment['blog_post_title'] = $blog_post['title'];
        $comment['approve_comment_url'] = BASE_URL_ADMIN.'/admin_blog_comments.php?action=approve&id='.$comment_id;
        $comment['view_comment_url'] = BASE_URL_ADMIN.'/admin_blog_comments.php?action=edit&id='.$comment_id;
        $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued,data) SELECT user_id, 'user', user_id, 'blog_post_followers_update', NOW(), ? FROM ".T_BLOG_FOLLOWERS." WHERE blog_id=? AND user_id!=?",array(serialize(array('variables'=>$comment)),$blog_post['id'],$comment['user_id']));
    }

    /**
    * Insert blog post
    * @param array $data Data to insert
    */
    function insert($data) {
        $data['friendly_url'] = Strings::rewrite($data['friendly_url']);
        if(!isset($data['user_id'])) {
            $data['user_id'] = $this->PMDR->get('Session')->get('admin_id');
        }
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();

        $blog_id = parent::insert($data);

        if(!empty($data['categories'])) {
            foreach($data['categories'] AS $category) {
                $this->db->Execute("INSERT INTO ".T_BLOG_CATEGORIES_LOOKUP." (blog_id,category_id) VALUES (?,?)",array($blog_id,$category));
            }
        }
        if(!empty($data['image'])) {
            if($extension = $this->PMDR->get('Image_Handler')->process($data['image'],BLOG_PATH.$blog_id.'.*')) {
                $this->update(array('image_extension'=>$extension),$blog_id);
            }
        }
        return $blog_id;
    }

    /**
    * Update blog post
    * @param array $data
    * @param int $id
    */
    function update($data, $id) {
        if($data['image_delete']) {
            unlink(find_file(BLOG_PATH.$id.'.*'));
        }
        $data['date_update'] = $this->PMDR->get('Dates')->dateTimeNow();
        if(!empty($data['friendly_url'])) {
            $data['friendly_url'] = Strings::rewrite($data['friendly_url']);
        }
        if(!empty($data['categories'])) {
            $this->db->Execute("DELETE FROM ".T_BLOG_CATEGORIES_LOOKUP." WHERE blog_id=?",array($id));
            foreach($data['categories'] AS $category) {
                $this->db->Execute("INSERT INTO ".T_BLOG_CATEGORIES_LOOKUP." (blog_id,category_id) VALUES (?,?)",array($id,$category));
            }
        }
        if(!empty($data['image'])) {
            if($extension = $this->PMDR->get('Image_Handler')->process($data['image'],BLOG_PATH.$id.'.*')) {
                $data['image_extension'] = $extension;
            }
        }
        parent::update($data,$id);
    }

    /**
    * Delete a blog post
    * @param int $id
    * @return resource
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_BLOG_CATEGORIES_LOOKUP." WHERE blog_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_BLOG_COMMENTS." WHERE blog_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_BLOG_FOLLOWERS." WHERE blog_id=?",array($id));
        unlink_file(find_file(BLOG_PATH.$id.'.*'));
        parent::delete($id);
    }

    /**
    * Follow a blog post
    * @param int $id
    * @param int $user_id
    */
    function follow($id, $user_id) {
        $this->db->Execute("REPLACE INTO ".T_BLOG_FOLLOWERS." (blog_id,user_id) VALUES (?,?)",array($id,$user_id));
    }

    /**
    * Unfollow a blog post
    * @param int $id
    * @param int $user_id
    */
    function unfollow($id, $user_id) {
        $this->db->Execute("DELETE FROM ".T_BLOG_FOLLOWERS." WHERE blog_id=? AND user_id=?",array($id,$user_id));
    }

    /**
    * Approve a blog post comment
    *
    * @param int $id Comment ID
    * @return boolean
    */
    function approveComment($id) {
        $blog = $this->db->GetRow("SELECT b.id, b.user_id, c.id AS comment_id, c.comment FROM ".T_BLOG." b INNER JOIN ".T_BLOG_COMMENTS." c WHERE c.id=?",array($id));
        if($blog) {
            $this->db->Execute("UPDATE ".T_BLOG_COMMENTS." SET status='active' WHERE id=?",array($blog['comment_id']));
            $this->PMDR->get('Blog')->sendFollowersEmail($blog['comment_id']);
            $variables['comment'] = $blog['comment'];
            $this->PMDR->get('Email_Templates')->send('blog_comment_submitted',array('to'=>$blog['user_id'],'blog_id'=>$blog['id'],'variables'=>$variables));
            return true;
        } else {
            return false;
        }
    }

    /**
    * Insert a blog post comment
    *
    * @param array $data Comment data
    * @param int $id Blog post ID
    */
    function insertComment($data,$id) {
        if(!$data['user_id'] = $this->PMDR->get('Session')->get('user_id')) {
            $data['user_id'] = NULL;
        }

        $data['status'] = 'pending';

        $this->db->Execute("INSERT INTO ".T_BLOG_COMMENTS." (user_id,blog_id,name,email,website,status,date,comment) VALUES
        (?,?,?,?,?,?,NOW(),?)",array($data['user_id'],$id,(string) $data['name'],(string) $data['email'],(string) $data['website'],$data['status'],$data['comment']));

        $comment_id = $this->db->Insert_ID();

        $variables['approve_comment_url'] = BASE_URL_ADMIN.'/admin_blog_comments.php?action=approve&id='.$comment_id;
        $variables['view_comment_url'] = BASE_URL_ADMIN.'/admin_blog_comments.php?action=edit&id='.$comment_id;
        $variables['comment'] = $data['comment'];

        $this->PMDR->get('Email_Templates')->send('admin_blog_comment_submitted',array('blog_id'=>$id,'variables'=>$variables));

        if($data['follow'] AND !is_null($data['user_id'])) {
            $this->follow($record['id'],$data['user_id']);
        }
    }
}
?>