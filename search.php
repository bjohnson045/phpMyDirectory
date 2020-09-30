<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_search'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_search'));
$PMDR->set('meta_robots','noindex,nofollow');
$PMDR->set('meta_title',coalesce($PMDR->getConfig('search_meta_title'),$PMDR->getLanguage('public_search')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('search_meta_description'),$PMDR->getLanguage('public_search')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/search.php','text'=>$PMDR->getLanguage('public_search')));

$PMDR->config['search_display_all'] = false;

$category_count = $PMDR->get('Categories')->getCount();
$location_count = $PMDR->get('Locations')->getCount();

$form = $PMDR->getNew('Form');
$form->method = 'GET';
$form->action = 'search_results.php';
$form->addFieldSet('advanced_search',array('legend'=>$PMDR->getLanguage('public_search')));
$form->addField('keyword','text',array('label'=>$PMDR->getLanguage('public_search_keyword'),'fieldset'=>'advanced_search'));
if($category_count > 1) {
    if($PMDR->getConfig('category_select_type') == 'tree_select') {
        $form->addField('category','tree_select',array('label'=>$PMDR->getLanguage('public_search_category'),'fieldset'=>'advanced_search','value'=>$_GET['category'],'first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(array('hidden'=>0))));
    } elseif($PMDR->getConfig('category_select_type') == 'tree_select_cascading') {
        $form->addField('category','tree_select_cascading',array('label'=>$PMDR->getLanguage('public_search_category'),'fieldset'=>'advanced_search','value'=>$_GET['category'],'options'=>array('type'=>'category_tree','hidden'=>0,'search'=>true)));
    } else {
        $form->addField('category','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('public_search_category'),'fieldset'=>'advanced_search','value'=>$_GET['category'],'options'=>array('type'=>'category_tree','hidden'=>0,'search'=>true)));
    }
} else {
    $form->addField('category','hidden',array('label'=>$PMDR->getLanguage('public_search_category'),'fieldset'=>'advanced_search','value'=>$PMDR->get('Categories')->getOneID()));
}
if($location_count > 1) {
    if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
        $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('public_search_location'),'fieldset'=>'advanced_search','value'=>$_GET['location'],'first_option'=>'','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 AND hidden=0 ORDER BY left_")));
    } else {
        $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('public_search_location'),'fieldset'=>'advanced_search','value'=>$_GET['location'],'options'=>array('type'=>'location_tree','hidden'=>0,'search'=>true)));
    }
} else {
    $form->addField('location_id','hidden',array('label'=>$PMDR->getLanguage('public_search_location'),'fieldset'=>'advanced_search','value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
}
$form->addField('location','text',array('label'=>$PMDR->getLanguage('public_search_location_text'),'fieldset'=>'advanced_search'));
$form->addField('zip','text',array('label'=>$PMDR->getLanguage('public_search_zip'),'fieldset'=>'advanced_search'));
$form->addField('zip_miles','select',array('label'=>$PMDR->getLanguage('public_search_distance'),'fieldset'=>'advanced_search','value'=>'5','options'=>array('1'=>'1','5'=>'5','10'=>'10','25'=>'25','50'=>'50','100'=>'100','200'=>'200','500'=>'500')));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit'),'fieldset'=>'button'));

$form_products = $PMDR->getNew('Form');
$form_products->setName('form_products');
$form_products->method = 'GET';
$form_products->action = 'search_classifieds.php';
$form_products->addFieldSet('classifieds_search',array('legend'=>$PMDR->getLanguage('public_search_classifieds')));
$form_products->addField('keyword','text',array('id'=>'form_products_keyword','label'=>$PMDR->getLanguage('public_search_keyword'),'fieldset'=>'classifieds_search'));
$form_products->addField('submit','submit',array('id'=>'form_products_submit','label'=>$PMDR->getLanguage('public_submit'),'fieldset'=>'submit'));

$form_documents = $PMDR->getNew('Form');
$form_documents->setName('form_documents');
$form_documents->method = 'GET';
$form_documents->action = 'search_documents.php';
$form_documents->addFieldSet('documents_search',array('legend'=>$PMDR->getLanguage('public_search_documents')));
$form_documents->addField('keyword','text',array('id'=>'form_documents_keyword','label'=>$PMDR->getLanguage('public_search_keyword'),'fieldset'=>'documents_search'));
$form_documents->addField('submit','submit',array('id'=>'form_documents_submit','label'=>$PMDR->getLanguage('public_submit'),'fieldset'=>'submit'));

$form_images = $PMDR->getNew('Form');
$form_images->setName('form_images');
$form_images->method = 'GET';
$form_images->action = 'search_images.php';
$form_images->addFieldSet('images_search',array('legend'=>$PMDR->getLanguage('public_search_images')));
$form_images->addField('keyword','text',array('id'=>'form_images_keyword','label'=>$PMDR->getLanguage('public_search_keyword'),'fieldset'=>'images_search'));
$form_images->addField('submit','submit',array('id'=>'form_images_submit','label'=>$PMDR->getLanguage('public_submit'),'fieldset'=>'submit'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'search.tpl');
$template_content->set('form',$form);
$template_content->set('form_products',$form_products);
$template_content->set('form_documents',$form_documents);
$template_content->set('form_images',$form_images);

include(PMDROOT.'/includes/template_setup.php');
?>