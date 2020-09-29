<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_listings','admin_listings_search'));

$PMDR->get('Authentication')->checkPermission('admin_listings_search');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$template_content->set('title',$PMDR->getLanguage('admin_listings_search'));
$form = $PMDR->get('Form');
$form->method = 'GET';
$form->action = 'admin_listings.php';
$form->addFieldSet('search');

$form->addField('listing_id','text',array('label'=>$PMDR->getLanguage('admin_listings_search_listing_id'),'fieldset'=>'search'));
$form->addField('keywords','text',array('label'=>$PMDR->getLanguage('admin_listings_search_keywords'),'fieldset'=>'search'));
$form->addField('phone','text',array('label'=>$PMDR->getLanguage('admin_listings_search_phone'),'fieldset'=>'search'));
$form->addField('www','text_autocomplete',array('label'=>$PMDR->getLanguage('admin_listings_search_www'),'fieldset'=>'search','data'=>'autocomplete_listings_www'));
$form->addField('zip','text',array('label'=>$PMDR->getLanguage('admin_listings_search_zip'),'fieldset'=>'search'));
if($db->GetOne("SELECT COUNT(*) AS count FROM ".T_ZIP_DATA)) {
    $form->addField('zip_miles','select',array('label'=>$PMDR->getLanguage('admin_listings_search_miles'),'fieldset'=>'search','options'=>array(''=>'-',1=>'1',10=>'10',25=>'25',50=>'50',100=>'100',300=>'300',500=>'500')));
}
$form->addField('email','text_autocomplete',array('label'=>$PMDR->getLanguage('admin_listings_search_email'),'fieldset'=>'search','data'=>'autocomplete_listings_email'));
$form->addField('category','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_listings_search_category'),'fieldset'=>'search','value'=>'','options'=>array('type'=>'category_tree','bypass_setup'=>true)));
$form->addField('location','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_listings_search_location'),'fieldset'=>'search','value'=>'','options'=>array('type'=>'location_tree','bypass_setup'=>true)));
$form->addField('claimed','checkbox',array('label'=>$PMDR->getLanguage('admin_listings_claimed'),'fieldset'=>'search'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$template_content->set('content',$form->toHTML());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_listings_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>