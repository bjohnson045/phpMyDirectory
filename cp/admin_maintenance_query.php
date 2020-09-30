<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_query');

$form = $PMDR->get('Form');
$form->addFieldSet('sql_statements',array('legend'=>$PMDR->getLanguage('admin_maintenance_query_sql')));
$form->addField('query','textarea',array('label'=>$PMDR->getLanguage('admin_maintenance_query_sql'),'fieldset'=>'sql_statements'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$form->addValidator('query',new Validate_NonEmpty());

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if($form->wasSubmitted('submit')) {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','Database query is disabled in the demo.');
    } else {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            try {
                if($db->Execute($data['query'])) {
                    $PMDR->addMessage('success',$PMDR->getLanguage('admin_maintenance_query_executed',array($data['query'])));
                }
            } catch (Exception $e) {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_maintenance_query_executed',array($db->getErrorMessage($data['query']))));
            }
        }
    }
}

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_query'));
$template_content->set('content',$form->toHTML());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>