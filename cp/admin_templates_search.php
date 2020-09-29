<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');
      
$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_templates'));

$PMDR->get('Authentication')->checkPermission('admin_templates_view');

/** @var Templates */
$templates = $PMDR->get('Templates');                               
     
$form = $PMDR->get('Form');
$form->action = 'admin_templates_data.php';
$form->method = 'GET';
$form->addFieldSet('search',array('legend'=>$PMDR->getLanguage('admin_templates_search')));
$form->addField('template','select',array('label'=>$PMDR->getLanguage('admin_templates_template'),'fieldset'=>'search','options'=>$db->GetAssoc("SELECT id, title FROM ".T_TEMPLATES." ORDER BY title")));
$form->addField('keyword','text',array('label'=>$PMDR->getLanguage('admin_templates_search_for'),'fieldset'=>'search'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_templates_search'));  
$template_content->set('content',$form->toHTML());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_templates_menu.tpl'); 
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>