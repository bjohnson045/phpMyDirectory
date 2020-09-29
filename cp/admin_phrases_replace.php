<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_languages'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_phrases_findreplace');

$form = $PMDR->get('Form');
$phrases = $PMDR->get('Phrases');
$languages = $PMDR->get('Languages');

// If no language ID is set, get the default one for display
$_GET['id'] = (isset($_GET['id']) ? $_GET['id'] : $db->GetOne("SELECT languageid FROM ".T_LANGUAGES." WHERE isdefault=1"));

if(!isset($_GET['action'])) {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_languages_replace')));
    $form->addField('id','select',array('label'=>$PMDR->getLanguage('admin_languages_replace_language'),'value'=>$_GET['id'],'options'=>$db->GetAssoc("SELECT languageid, title FROM ".T_LANGUAGES)));
    $form->addField('find','text',array('label'=>$PMDR->getLanguage('admin_languages_replace_find')));
    $form->addField('replace','text',array('label'=>$PMDR->getLanguage('admin_languages_replace_with')));
    $section_options = $db->GetAssoc("SELECT DISTINCT(section) AS section, section FROM ".T_LANGUAGE_PHRASES." ORDER BY section ASC");
    $form->addField('section','text_select',array('label'=>$PMDR->getLanguage('admin_languages_replace_section'),'options'=>$section_options,'limit'=>10));
    $form->addField('case_sensitive','checkbox',array('label'=>$PMDR->getLanguage('admin_languages_replace_case'),'value'=>$_GET['case_sensitive']));
    $form->addField('match_exact','checkbox',array('label'=>$PMDR->getLanguage('admin_languages_replace_match'),'value'=>$_GET['match_exact']));
    $form->addField('translated','checkbox',array('label'=>$PMDR->getLanguage('admin_languages_replace_translated'),'value'=>$_GET['translated']));
    $form->addField('find_replace','submit',array('label'=>$PMDR->getLanguage('admin_languages_replace_preview'),'fieldset'=>'submit'));

    $form->addValidator('find',new Validate_NonEmpty());
    $form->addValidator('replace',new Validate_NonEmpty());

    $template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_phrases_replace.tpl');

    if($form->wasSubmitted('find_replace')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $phrase_sql = $form->getFieldValue('case_sensitive') ? 'binary' : '';
            $phrase_sql .= $form->getFieldValue('translated') ? " phrases.content" : " master.content";
            $phrase_sql .= $form->getFieldValue('match_exact') ? " LIKE '".$form->getFieldValue('find')."'" : " LIKE '%".$form->getFieldValue('find')."%'";
            if(!empty($data['section'])) {
                $phrase_sql .= ' AND master.section IN('.$PMDR->get('Cleaner')->clean_db(implode('\',\'',$data['section'])).')';
            }
            $record_count = $db->GetOne("
            SELECT COUNT(*)
            FROM ".T_LANGUAGE_PHRASES." as master LEFT JOIN ".T_LANGUAGE_PHRASES." as phrases ON master.variablename=phrases.variablename AND master.section=phrases.section AND phrases.languageid=?
            WHERE master.languageid=-1 AND $phrase_sql",array($form->getFieldValue('id')));

            $table_list = $PMDR->get('TableList');
            $table_list->all_one_page = true;
            $table_list->addCheckbox(array(),'id',false);
            $table_list->addColumn('content_master',$PMDR->getLanguage('admin_languages_replace_default'));
            $table_list->addColumn('content',$PMDR->getLanguage('admin_languages_replace_current'));
            $table_list->addColumn('content_new',$PMDR->getLanguage('admin_languages_replace_phrase_replaced'));
            $table_list->addColumn('master_variablename',$PMDR->getLanguage('admin_languages_variable'));
            $table_list->addColumn('master_section',$PMDR->getLanguage('admin_languages_section'));
            $table_list->setTotalResults($record_count);
            $records = $db->GetAll("
            SELECT CONCAT(master.section,'-',master.variablename) AS id, master.variablename as master_variablename, master.section as master_section, COALESCE(phrases.content,master.content) as content, master.content as content_master
            FROM ".T_LANGUAGE_PHRASES." as master LEFT JOIN ".T_LANGUAGE_PHRASES." as phrases ON master.variablename=phrases.variablename AND master.section=phrases.section AND phrases.languageid=?
            WHERE master.languageid=-1 AND $phrase_sql
            ORDER BY master.variablename LIMIT ".$table_list->page_data['limit1'].", ".$table_list->page_data['limit2'],array($form->getFieldValue('id')));
            foreach($records as $key=>$record) {
                $records[$key]['content'] = preg_replace('/('.preg_quote($form->getFieldValue('find'),'/').')/iu','<span style="color: #e82a2a; font-weight: bold;">$1</span>',$record['content']);
                $records[$key]['content_new'] = preg_replace('/('.preg_quote($form->getFieldValue('find'),'/').')/iu','<span style="color: #4dda2c; font-weight: bold;">'.$form->getFieldValue('replace').'</span>',$record['content']);
            }
            $table_list->addRecords($records);

            if($record_count) {
                $form->addField('perform_replace','submit',array('label'=>$PMDR->getLanguage('admin_languages_replace_perform'),'fieldset'=>'submit','class'=>array('btn-warning'),'onclick'=>'return confirm(\''.$PMDR->getLanguage('messages_confirm').'\');'));
            }
            $template_content->set('table_list',$table_list->render());
        }
    }
    if($form->wasSubmitted('perform_replace')) {
        $data = $form->loadValues();
        $phrase_sql = $form->getFieldValue('case_sensitive') ? 'binary' : '';
        $phrase_sql .= $form->getFieldValue('translated') ? " phrases.content" : " master.content";
        $phrase_sql .= $form->getFieldValue('match_exact') ? " LIKE '".$form->getFieldValue('find')."'" : " LIKE '%".$form->getFieldValue('find')."%'";
        if($data['section'] != '') {
            $phrase_sql .= ' AND master.section LIKE '.$PMDR->get('Cleaner')->clean_db($data['section'].'%');
        }
        $replace_list = $db->GetAll("
        SELECT master.variablename as master_variablename, master.section as master_section, COALESCE(phrases.content,master.content) as content, master.content as content_master
        FROM ".T_LANGUAGE_PHRASES." as master LEFT JOIN ".T_LANGUAGE_PHRASES." as phrases ON master.variablename=phrases.variablename AND master.section=phrases.section AND phrases.languageid=?
        WHERE master.languageid=-1 AND $phrase_sql",array($form->getFieldValue('id')));

        foreach($replace_list as $phrase) {
            if(!in_array($phrase['master_section'].'-'.$phrase['master_variablename'],$_POST['table_list_checkboxes'])) {
                continue;
            }
            if($form->getFieldValue('case_sensitive')) {
                $new_content = str_replace($form->getFieldValue('find'),$form->getFieldValue('replace'),$phrase['content']);
            } else {
                $new_content = preg_replace('/('.preg_quote($form->getFieldValue('find'),'/').')/iu',$form->getFieldValue('replace'),$phrase['content']);
            }
            $phrases->updatePhrase($form->getFieldValue('id'), $phrase['master_section'], $phrase['master_variablename'], $new_content);
        }

        $PMDR->addMessage('success',$PMDR->getLanguage('admin_languages_replace_complete'),'insert');
        $PMDR->get('Cache')->deletePrefix('language_');
        redirect();
    }
    $template_content->set('form',$form);
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_languages_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>