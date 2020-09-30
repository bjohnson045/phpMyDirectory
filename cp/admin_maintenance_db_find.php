<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_db_find');

$tables = $db->GetAll("SHOW TABLE STATUS");
$tables_select = array(''=>'');
foreach($tables as $table) {
    $tables_select[$table['Name']] = $table['Name'];
}

$form = $PMDR->get('Form');
$form->addFieldSet('sql_statements',array('legend'=>$PMDR->getLanguage('admin_maintenance_db_find')));
// Add onload code to do the AJAX lookup on reload if no term is found.

$form->addField('table','select',array('label'=>$PMDR->getLanguage('admin_maintenance_db_find_table'),'help'=>$PMDR->getLanguage('admin_maintenance_db_find_table_help'),'fieldset'=>'sql_statements','value'=>'','options'=>$tables_select,'onchange'=>'$.ajax({ data: ({ action: \'admin_maintenance_db_find_get_columns\',table: $(\'#table\').val() }), success: function(options) { $(\'#field > option\').remove();$.each(options, function(val, text) {$(\'#field\').append($(\'<option></option>\').val(text).html(text));});},dataType: \'json\' });'));
$form->addField('field','select',array('label'=>$PMDR->getLanguage('admin_maintenance_db_find_field'),'help'=>$PMDR->getLanguage('admin_maintenance_db_find_field_help'),'fieldset'=>'sql_statements'));
$form->addField('find','text',array('label'=>$PMDR->getLanguage('admin_maintenance_db_find_find'),'help'=>$PMDR->getLanguage('admin_maintenance_db_find_find_help'),'fieldset'=>'sql_statements'));
$form->addField('replace','text',array('label'=>$PMDR->getLanguage('admin_maintenance_db_find_replace'),'help'=>$PMDR->getLanguage('admin_maintenance_db_find_replace_help'),'fieldset'=>'sql_statements'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$form->addValidator('table',new Validate_NonEmpty());
$form->addValidator('field',new Validate_NonEmpty());
$form->addValidator('find',new Validate_NonEmpty());

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$javascript = '';

if($form->wasSubmitted('submit')) {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','Database find and replace disabled in the demo.');
    } else {
        $form->loadValues();

        $javascript = '
        <script type="text/javascript">
        $(document).ready(function() {
            if($(\'#table\').val() != \'\') {
                $.ajax({ data: ({ action: \'admin_maintenance_db_find_get_columns\',table: $(\'#table\').val() }), success: function(options) { $(\'#field > option\').remove();$.each(options, function(val, text) {$(\'#field\').append($(\'<option></option>\').val(text).html(text));}); $(\'#field\').val(\''.$form->getFieldValue('field').'\');},dataType: \'json\' });

            }
        });
        </script>
        ';

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $instances = $db->GetOne("SELECT COUNT(*) AS count FROM ".$form->getFieldValue('table')." WHERE ".$form->getFieldValue('field')." LIKE ".$PMDR->get('Cleaner')->clean_db("%".$form->getFieldValue('find')."%"));
            if($instances) {
                $db->Execute("UPDATE ".$form->getFieldValue('table')." SET ".$form->getFieldValue('field')." = REPLACE(".$form->getFieldValue('field').",?,?)",array($form->getFieldValue('find'),$form->getFieldValue('replace')));
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_maintenance_db_find_replaced',array($instances,$form->getFieldValue('find'))));
            } else {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_maintenance_db_find_not_found',$form->getFieldValue('find')));
            }
        }
    }
}

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_db_find'));

$template_content->set('content',$javascript.$form->toHTML());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>