<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_blog'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_blog_categories'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('blog_meta_title'),$PMDR->getLanguage('public_blog')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/blog.php','text'=>$PMDR->getLanguage('public_blog')));
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('public_blog_categories')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blog_categories.tpl');

$categories = $db->GetAll("SELECT c.*, IFNULL(COUNT(bcl.blog_id),0) AS post_count FROM ".T_BLOG_CATEGORIES." c LEFT JOIN ".T_BLOG_CATEGORIES_LOOKUP." bcl ON c.id=bcl.category_id LEFT JOIN ".T_BLOG." b ON bcl.blog_id=b.id AND b.status='active' AND DATE(b.date_publish) <= CURDATE() GROUP BY c.id ORDER BY title ASC");

$category_titles = array();

foreach($categories AS $key=>$category) {
    $categories[$key]['url'] = $PMDR->Get('Blog')->getCategoryURL($category['id'],$category['friendly_url']);
    $category_titles[] = $category['title'];
}

$PMDR->set('meta_description',coalesce($PMDR->getConfig('blog_meta_description'),($PMDR->getLanguage('public_blog').', '.implode(' ',$category_titles))));
$PMDR->set('meta_keywords',implode(' ',$category_titles));

$template_content->set('categories',$categories);

include(PMDROOT.'/includes/template_setup.php');
?>