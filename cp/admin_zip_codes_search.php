<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_zip_codes'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_zip_codes_view');

/** @var Zip_Codes */
$zip_codes = $PMDR->get('Zip_Codes');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_zip_codes_search'));

if(!isset($_GET['action'])) {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_zip_codes_search')));
    $form->addField('zipcode','text',array('label'=>$PMDR->getLanguage('admin_zip_codes_zip'),'fieldset'=>'information'));
    $form->addField('radius','select',array('label'=>$PMDR->getLanguage('admin_zip_codes_search_radius'),'fieldset'=>'information','options'=>array(''=>'None','1'=>'1','5'=>'5','10'=>'10','25'=>'25','50'=>'50','100'=>'100','250'=>'250')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('zipcode',new Validate_NonEmpty());
    
    if($form->wasSubmitted('submit')) {
        $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            redirect('admin_zip_codes.php',array('action'=>'search','zipcode'=>$form->getFieldValue('zipcode'),'radius'=>$form->getFieldValue('radius')));    
        } 
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_zip_codes_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>