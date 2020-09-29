<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_languages'));

$PMDR->get('Authentication')->checkPermission('admin_languages_view');

$form = $PMDR->get('Form');

$languages = $PMDR->get('Languages');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_languages_delete');
    if($_GET['id'] != 1) {
        $languages->delete($_GET['id']);
        $PMDR->get('Cache')->deletePrefix('language_');
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_languages'))),'delete');
    }
    redirect();
}

if($_GET['action'] == 'export') {
    $PMDR->get('Authentication')->checkPermission('admin_phrases_export');
    $languages->export($_GET['id']);
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title',$PMDR->getLanguage('admin_languages_title'));
    $table_list->addColumn('active',$PMDR->getLanguage('admin_languages_active'));
    $table_list->addColumn('charset',$PMDR->getLanguage('admin_languages_charset'));
    $table_list->addColumn('languagecode',$PMDR->getLanguage('admin_languages_iso'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_LANGUAGES));
    $records = $languages->GetAll();

    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']).' (<a href="admin_phrases.php?id='.$record['languageid'].'">'.$PMDR->getLanguage('admin_languages_view_phrases').'</a>)';
        $record['charset'] = $PMDR->get('Cleaner')->clean_output($record['charset']);
        $record['languagecode'] = $PMDR->get('Cleaner')->clean_output($record['languagecode']);
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['languageid']));
        $record['manage'] .= $PMDR->get('HTML')->icon('download',array('label'=>'Export','href'=>URL_NOQUERY.'?action=export&id='.$record['languageid']));
        if($record['languageid'] != 1) {
            $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['languageid']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_languages'));
    $template_content->set('content',$table_list->render());
} else {
    $language = $languages->find($_GET['id']);

    $PMDR->get('Authentication')->checkPermission('admin_languages_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('details',array('legend'=>$PMDR->getLanguage('admin_languages_information')));
    $form->addFieldSet('format');
    $form->addFieldSet('phrases',array('legend'=>$PMDR->getLanguage('admin_languages_import')));
    if(value($language,'isdefault')) {
        $form->addField('active','hidden',array('fieldset'=>'details','value'=>1));
    } elseif($_GET['action'] != 'edit' OR !value($language,'isdefault')) {
        $form->addField('active','checkbox',array('fieldset'=>'details'));
    }
    $form->addField('title','text',array('fieldset'=>'details'));
    $form->addField('languagecode','text',array('label'=>$PMDR->getLanguage('admin_languages_iso'),'fieldset'=>'details','value'=>'en-us'));
    $form->addField('charset','text',array('fieldset'=>'details','value'=>'UTF-8'));
    $form->addField('textdirection','select',array('label'=>$PMDR->getLanguage('admin_languages_text_direction'),'fieldset'=>'format','value'=>'ltr','options'=>array('ltr'=>'Left to Right','rtl'=>'Right to Left')));
    $form->addField('decimalseperator','text',array('label'=>$PMDR->getLanguage('admin_languages_decimal'),'fieldset'=>'format','value'=>'.'));
    $form->addField('thousandseperator','text',array('label'=>$PMDR->getLanguage('admin_languages_thousands'),'fieldset'=>'format','value'=>','));
    $form->addField('decimalplaces','text',array('fieldset'=>'format','value'=>'2'));
    $form->addField('currency_prefix','text',array('fieldset'=>'format','value'=>'$','no_trim'=>true));
    $form->addField('currency_suffix','text',array('fieldset'=>'format','value'=>' USD','no_trim'=>true));
    $form->addField('date_override','text',array('fieldset'=>'format'));
    $form->addField('time_override','text',array('fieldset'=>'format'));
    $form->addField('locale','select',array('fieldset'=>'details','first_option'=>array(''=>'Automatic'),'options'=>include(PMDROOT.'/includes/locales.php')));
    $form->addField('phrase_csv','file',array('fieldset'=>'phrases'));
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('languagecode',new Validate_NonEmpty());
    $form->addValidator('charset',new Validate_NonEmpty());
    $form->addValidator('decimalseperator',new Validate_NonEmpty());
    $form->addValidator('thousandseperator',new Validate_NonEmpty());
    $form->addValidator('decimalplaces',new Validate_NonEmpty());
    $form->addValidator('decimalplaces',new Validate_Numeric_Range(0,9));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_languages_edit'));
        $form->loadValues($language);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_languages_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(value($data['phrase_csv'],'tmp_name') != '') {
            $csv_line = fgetcsv($csv = fopen($data['phrase_csv']['tmp_name'],'r'), 4096,',','"');  // get rid of the first line and use it to test compatibility
            if(count($csv_line) != 3 AND count($csv_line) != 4) {
                $form->addError($PMDR->getLanguage('admin_languages_phrase_csv_format_error'),'phrase_csv');
            }
            $line = 1;
            while($csv_line = fgetcsv($csv, 0,',','"')) {
                if($csv_line[0] == '') {
                    $form->addError($PMDR->getLanguage('admin_languages_phrase_csv_format_error',array($line)),'phrase_csv');
                    break;
                }
                $line++;
            }
            fclose($csv);
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Cache')->deletePrefix('language_');
            if($_GET['action']=='add') {
                $languages->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_languages'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $languages->update($_GET['id'], $data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_languages'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_languages_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>