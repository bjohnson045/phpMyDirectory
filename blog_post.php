<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_blog','email_templates'));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/blog.php','text'=>$PMDR->getLanguage('public_blog')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blog_post.tpl');

if(!$record = $db->GetRow("SELECT * FROM ".T_BLOG." b WHERE DATE(b.date_publish) <= CURDATE() AND b.status='active' AND id=?",array($_GET['id']))) {
    $PMDR->get('Error',404);
}

$record['url'] = $PMDR->get('Blog')->getURL($record['id'],$record['friendly_url']);

if(URL != $record['url']) {
    $PMDR->get('Error',301);
    redirect($record['url']);
}

$categories = $db->GetAll("SELECT id, title, friendly_url FROM ".T_BLOG_CATEGORIES." bc INNER JOIN ".T_BLOG_CATEGORIES_LOOKUP." bcl ON bc.id=bcl.category_id WHERE bcl.blog_id=?",array($record['id']));

$PMDR->set('og:type','article');

$PMDR->set('meta_title',coalesce($record['meta_title'],$record['title']));
$PMDR->set('meta_description',coalesce($record['meta_description'],strip_tags($record['content_short'])));
$PMDR->set('meta_keywords',coalesce($record['meta_keywords'],$record['keywords']));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_blog'));
$PMDR->setAdd('page_title',$record['title']);
$PMDR->setAddArray('breadcrumb',array('text'=>$record['title']));

$template_blog_categories = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_post_categories.tpl');
$template_blog_categories_array = array();
foreach($categories AS $key=>$category) {
    $template_blog_categories_array[$key] = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_post_category.tpl');
    $template_blog_categories_array[$key]->set('title',$category['title']);
    $template_blog_categories_array[$key]->set('url',$PMDR->Get('Blog')->getCategoryURL($category['id'],$category['friendly_url']));
    $template_blog_categories_array[$key] = $template_blog_categories_array[$key]->render();
}
$template_blog_categories->set('categories',$template_blog_categories_array);

if($PMDR->getConfig('blog_comments')) {
    $comments = $db->GetAll("SELECT bc.* FROM ".T_BLOG_COMMENTS." bc WHERE bc.blog_id=? AND status='active'",array($record['id']));
    $template_comments = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_comments.tpl');
    $template_comments_array = array();
    foreach($comments AS $key=>$comment) {
        $template_comments_array[$key] = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_comment.tpl');
        $template_comments_array[$key]->set('user_id',$comment['user_id']);
        if(!is_null($comment['user_id'])) {
            $template_comments_array[$key]->set('name',$db->GetOne("SELECT COALESCE(NULLIF(TRIM(user_first_name),''),login) FROM ".T_USERS." WHERE id=?",array($comment['user_id'])));
            //$template_comments_array[$key]->set('name_url',$comment['website']);
        } else {
            $template_comments_array[$key]->set('name',$comment['name']);
            //$template_comments_array[$key]->set('name_url',$comment['website']);
        }
        $template_comments_array[$key]->set('date',$PMDR->get('Dates_Local')->formatDateTime($comment['date']));
        $template_comments_array[$key]->set('comment',$comment['comment']);
        $template_comments_array[$key]->set('website',$comment['website']);
        $template_comments_array[$key] = $template_comments_array[$key]->render();
    }
    $template_comments->set('comments',$template_comments_array);
    $template_content->set('comments',$template_comments);
    $template_content->set('comments_count',count($comments));
}

$template_content->set('date_updated',$PMDR->get('Dates_Local')->formatDateTime($record['date_updated']));
$template_content->set('date_publish',$PMDR->get('Dates_Local')->formatDate($record['date_publish']));
$template_content->set('date',$PMDR->get('Dates_Local')->formatDateTime($record['date'],true));
$template_content->set('status',$PMDR->getLanguage($record['status']));
$template_content->set('impressions',$record['impressions']);
$template_content->set('keywords',$record['keywords']);
$template_content->set('content',$record['content']);
$template_content->set('id',$record['id']);
$template_content->set('title',$record['title']);
$template_content->set('categories',$template_blog_categories);
if(is_null($record['user_display'])) {
    $template_content->set('user',$db->GetOne("SELECT COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login) AS user FROM ".T_USERS." WHERE id=?",array($record['user_id'])));
} else {
    $template_content->set('user',$record['user_display']);
}
if(file_exists(BLOG_PATH.$record['id'].'.'.$record['image_extension'])) {
    $template_content->set('image_url',get_file_url_cdn(BLOG_PATH.$record['id'].'.'.$record['image_extension']));
}

if(LOGGED_IN OR !$PMDR->getConfig('blog_comments_require_login')) {
    $form = $PMDR->getNew('Form');
    $form->addFieldSet('input_default',array('legend'=>$PMDR->getLanguage('public_blog_leave_comment')));
    if(!LOGGED_IN) {
        $form->addField('name','text',array('label'=>$PMDR->getLanguage('public_blog_post_name'),'fieldset'=>'input_default'));
        $form->addField('email','text',array('label'=>$PMDR->getLanguage('public_blog_post_email'),'fieldset'=>'input_default'));
        $form->addField('website','text',array('label'=>$PMDR->getLanguage('public_blog_post_website'),'fieldset'=>'input_default'));
        $form->addValidator('name',new Validate_NonEmpty());
        $form->addValidator('email',new Validate_NonEmpty());
        $template_content->set('followed',0);
    } else {
        $followed = $db->GetOne("SELECT COUNT(*) FROM ".T_BLOG_FOLLOWERS." WHERE blog_id=? AND user_id=?",array($record['id'],$PMDR->get('Session')->get('user_id')));
        $template_content->set('followed',$followed);
        if(!$followed) {
            $form->addField('follow','checkbox',array('label'=>$PMDR->getLanguage('public_blog_follow_post'),'fieldset'=>'input_default'));
        }
    }
    $form->addField('comment','textarea',array('label'=>$PMDR->getLanguage('public_blog_comment'),'fieldset'=>'input_default'));
    if($PMDR->getConfig('blog_comments_captcha') AND (!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in'))) {
        $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_blog_security_code'),'fieldset'=>'input_default'));
        $form->addValidator('security_code',new Validate_Captcha());
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit'),'fieldset'=>'button'));
    $template_content->set('form',$form);
} else {
    $template_content->set('login_url',BASE_URL.MEMBERS_FOLDER.'index.php?from='.urlencode_url(URL));
}

if($next_post = $db->GetRow("SELECT id, title, friendly_url FROM ".T_BLOG." b WHERE date > ? AND DATE(b.date_publish) <= CURDATE() AND b.status='active' ORDER BY date ASC",array($record['date']))) {
    $template_content->set('next_title',$next_post['title']);
    $template_content->set('next_url',$PMDR->get('Blog')->getURL($next_post['id'],$next_post['friendly_url']));
}
if($previous_post = $db->GetRow("SELECT id, title, friendly_url FROM ".T_BLOG." b WHERE date < ? AND DATE(b.date_publish) <= CURDATE() AND b.status='active' ORDER BY date DESC",array($record['date']))) {
    $template_content->set('previous_title',$previous_post['title']);
    $template_content->set('previous_url',$PMDR->get('Blog')->getURL($previous_post['id'],$previous_post['friendly_url']));
}

$template_content->set('share',$PMDR->get('Sharing')->getHTML());

$PMDR->get('Statistics')->insert('blog_impression',$record['id']);

if(isset($form) AND $form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $PMDR->get('Blog')->insertComment($data,$record['id']);
        $PMDR->addMessage('success','Comment submitted','insert');
        redirect(URL);
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>