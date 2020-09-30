<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_faq'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_faq'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('faq_meta_title'),$PMDR->getLanguage('public_faq')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('faq_meta_description'),$PMDR->getLanguage('public_faq')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/faq.php','text'=>$PMDR->getLanguage('public_faq')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'faq.tpl');

if(isset($_GET['id'])) {
    $question = $db->GetRow("SELECT * FROM ".T_FAQ_QUESTIONS." WHERE active=1 AND id=?",array($_GET['id']));
    $template_content->set('question',$question);
} else {
    $template_content->expire = 900;
    $template_content->cache_id = 'faq';
    if(!$template_content->isCached()) {
        $categories = $db->GetAssoc("SELECT * FROM ".T_FAQ_CATEGORIES." WHERE active=1 ORDER BY ordering ASC");
        $questions = $db->GetAssoc("SELECT * FROM ".T_FAQ_QUESTIONS." WHERE active=1 ORDER BY category_id DESC, ordering ASC");
        $template_content->set('categories',$categories);
        $template_content->set('questions',$questions);
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>