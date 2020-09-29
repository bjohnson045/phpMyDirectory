<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_blog'));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/blog.php','text'=>$PMDR->getLanguage('public_blog')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blog.tpl');

$paging = $PMDR->get('Paging');
$paging->setResultsNumber((int) $PMDR->getConfig('blog_posts_per_page'));

$form = $PMDR->getNew('Form');
$form->addField('keywords','text',array('fieldset'=>'contact_us','placeholder'=>$PMDR->getLanguage('public_blog_keywords')));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('search'),'fieldset'=>'button'));
$form->method = 'GET';
$form->loadValues();
$template_content->set('form',$form);

$where = array();
if(isset($_GET['category_id'])) {
    $category_join = "INNER JOIN ".T_BLOG_CATEGORIES_LOOKUP." bcl ON b.id=bcl.blog_id";
    $where[] = 'bcl.category_id='.$db->Clean($_GET['category_id']);
    if(!$category = $db->GetRow("SELECT * FROM ".T_BLOG_CATEGORIES." WHERE id=?",array($_GET['category_id']))) {
        $PMDR->get('Error',404);
    }
    $category['url'] = $PMDR->get('Blog')->getCategoryURL($category['id'],$category['friendly_url']);
    if(URL_NOQUERY != $category['url']) {
        $PMDR->get('Error',301);
        redirect($category['url']);
    }
    $template_content->set('category',$category['title']);
    if(!empty($category['meta_title'])) {
        $PMDR->set('meta_title',$category['meta_title']);
    } else {
        $PMDR->set('meta_title',$category['title'].' '.$PMDR->getLanguage('public_blog'));
    }
    $PMDR->setAdd('page_title',$PMDR->getLanguage('public_blog'));
    $PMDR->setAdd('page_title',$category['title']);
    $PMDR->set('meta_description',$category['meta_description']);
    if(!empty($category['meta_keywords'])) {
        $PMDR->set('meta_keywords',$category['meta_keywords']);
    } else {
        $PMDR->set('meta_keywords',$category['keywords']);
    }
    if(MOD_REWRITE) {
        $template_content->set('blog_url',BASE_URL.'/blog.html');
    } else {
        $template_content->set('blog_url',BASE_URL.'/blog.php');
    }
} else {
    $PMDR->setAdd('page_title',$PMDR->getLanguage('public_blog'));
    $PMDR->set('meta_title',coalesce($PMDR->getConfig('blog_meta_title'),$PMDR->getLanguage('public_blog')));
    $PMDR->set('meta_description',coalesce($PMDR->getConfig('blog_meta_description'),$PMDR->getLanguage('public_blog')));
}

if(isset($_GET['month'])) {
    $where[] = 'MONTH(b.date_publish) = '.$PMDR->get('Cleaner')->clean_db($_GET['month']);
}
if(isset($_GET['year'])) {
    $where[] = 'YEAR(b.date_publish) = '.$PMDR->get('Cleaner')->clean_db($_GET['year']);
}
if(isset($_GET['keywords'])) {
    $where[] = 'MATCH(title,keywords,content_short) AGAINST('.$db->Clean($_GET['keywords']).')';
}
if(isset($_GET['listing_id'])) {
    $where[] = 'listing_id = '.$PMDR->get('Cleaner')->clean_db($_GET['listing_id']);
}
if(isset($_GET['user_id'])) {
    $where[] = 'user_id = '.$PMDR->get('Cleaner')->clean_db($_GET['user_id']);
}

if(count($where)) {
    $where = 'AND '.implode(' AND ',$where);
} else {
    $where = '';
}

$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_BLOG." b $category_join WHERE DATE(b.date_publish) <= CURDATE() AND b.status='active' $where ORDER BY b.date DESC LIMIT ?,?",array($paging->limit1,$paging->limit2));
$paging->setTotalResults($db->FoundRows());
$page_array = $paging->getPageArray();
$template_page_navigation = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$page_array);
$template_content->set('page_navigation',$template_page_navigation);

if(count($records)) {
    foreach($records AS $record) {
        $categories = $db->GetAll("SELECT id, title, friendly_url FROM ".T_BLOG_CATEGORIES." bc INNER JOIN ".T_BLOG_CATEGORIES_LOOKUP." bcl ON bc.id=bcl.category_id WHERE bcl.blog_id=?",array($record['id']));

        $template_blog_categories = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_post_categories.tpl');
        $template_blog_categories_array = array();
        foreach($categories AS $key=>$category) {
            $template_blog_categories_array[$key] = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_post_category.tpl');
            $template_blog_categories_array[$key]->set('title',$category['title']);
            $template_blog_categories_array[$key]->set('url',$PMDR->Get('Blog')->getCategoryURL($category['id'],$category['friendly_url']));
            $template_blog_categories_array[$key] = $template_blog_categories_array[$key]->render();
        }
        $template_blog_categories->set('categories',$template_blog_categories_array);

        $template_post = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/blog_post_summary.tpl');
        $template_post->set('date',$PMDR->get('Dates_Local')->formatDateTime($record['date']));
        $template_post->set('date_updated',$PMDR->get('Dates_Local')->formatDateTime($record['date_updated']));
        $template_post->set('date_publish',$PMDR->get('Dates_Local')->formatDate($record['date_publish']));
        $template_post->set('status',$PMDR->getLanguage($record['status']));
        $template_post->set('impressions',$record['impressions']);
        $template_post->set('keywords',$record['keywords']);
        $template_post->set('comments',$PMDR->getConfig('blog_comments'));
        $template_post->set('comments_count',$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG_COMMENTS." WHERE blog_id=? AND status='active'",array($record['id'])));
        if(!empty($record['content_short'])) {
            $template_post->set('content',$record['content_short']);
        } elseif(strlen(strip_tags($record['content'])) > 300) {
            $template_post->set('content',Strings::limit_words(strip_tags($record['content']),300).' [...]');
        } else {
            $template_post->set('content',strip_tags($record['content']));
        }
        $template_post->set('title',$record['title']);
        $template_post->set('url',$PMDR->get('Blog')->getURL($record['id'],$record['friendly_url']));
        $template_post->set('categories',$template_blog_categories);
        if(is_null($record['user_display'])) {
            $template_post->set('user',$db->GetOne("SELECT COALESCE(NULLIF(user_display,''),NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login) AS user FROM ".T_USERS." WHERE id=?",array($record['user_id'])));
        } else {
            $template_post->set('user',$record['user_display']);
        }
        $template_posts .= $template_post->render();
    }
    $template_content->set('content',$template_posts);
} else {
    $template_content->set('records',false);
}

include(PMDROOT.'/includes/template_setup.php');
?>