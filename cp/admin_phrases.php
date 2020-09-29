<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_languages'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_phrases_view');

$form = $PMDR->get('Form');
$phrases = $PMDR->get('Phrases');
$languages = $PMDR->get('Languages');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_phrases_delete');
    $phrases->delete($_GET['id'],$_GET['section']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_languages_phrases'),'delete')));
    $PMDR->get('Cache')->deletePrefix('language_');
    redirect();
}

if($_GET['action'] == 'clear_updated') {
    $db->Execute("UPDATE ".T_LANGUAGE_PHRASES." SET updated=0");
    $PMDR->addMessage('success','Updated phrases have been cleared.');
    redirect();
}

// If no language ID is set, get the default one for display
$_GET['id'] = (isset($_GET['id']) ? $_GET['id'] : $db->GetOne("SELECT languageid FROM ".T_LANGUAGES." WHERE isdefault=1"));

// Default view
if(!isset($_GET['action'])) {
    $template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_phrases.tpl');

    $content_column = in_array('case_sensitive',(array) $_GET['options']) ? 'binary ' : '';

    // Handle search inputs
    if(in_array('translated',(array) $_GET['options'])) {
        $translated_sql = " AND phrases.content!=''";
        $content_column .= "phrases.content";
    } else {
        $content_column .= "master.content";
    }
    if(in_array('updated',(array) $_GET['options'])) {
        $template_content->set('updated',true);
        $updated_sql = " AND (master.updated=1 OR phrases.updated=1)";
    }

    if($_GET['keyword'] != '' AND !empty($_GET['filter'])) {
        if($_GET['keyword_options'] != 'EXACT') {
            $keywords = preg_split("/[\s]+/", $_GET['keyword'],NULL,PREG_SPLIT_NO_EMPTY);
        }
        if(count($keywords)) {
            $keyword_sql = ' AND (';
            if(in_array('phrase',(array) $_GET['filter'])) {
                if($_GET['keyword_options'] != 'EXACT') {
                    $keyword_string_parts = array();
                    foreach($keywords AS $keyword) {
                        $keyword_string_parts[] = "$content_column LIKE ".$db->Clean("%".$keyword."%");
                    }
                    $keyword_parts[] = '('.implode(' '.$_GET['keyword_options'].' ',$keyword_string_parts).')';
                } else {
                    $keyword_parts[] = "$content_column = ".$db->Clean($_GET['keyword']);
                }
            }
            if(in_array('variable',(array) $_GET['filter'])) {
                if($_GET['keyword_options'] != 'EXACT') {
                    $keyword_string_parts = array();
                    foreach($keywords AS $keyword) {
                        $keyword_string_parts[] = "master.variablename LIKE ".$db->Clean("%".$keyword."%");
                    }
                    $keyword_parts[] = '('.implode(' '.$_GET['keyword_options'].' ',$keyword_string_parts).')';
                } else {
                    $keyword_parts[] = "master.variablename LIKE ".$db->Clean($_GET['keyword']);
                }
            }
            if(in_array('section',(array) $_GET['filter'])) {
                if($_GET['keyword_options'] != 'EXACT') {
                    $keyword_string_parts = array();
                    foreach($keywords AS $keyword) {
                        $keyword_string_parts[] = "master.section LIKE ".$db->Clean("%".$keyword."%");
                    }
                    $keyword_parts[] = '('.implode(' '.$_GET['keyword_options'].' ',$keyword_string_parts).')';
                } else {
                    $keyword_parts[] = "master.section LIKE ".$db->Clean($_GET['keyword']);
                }
            }
            $keyword_sql .= implode(' OR ',$keyword_parts).')';
        }
    }

    $form = $PMDR->get('Form');
    $form->method = 'GET';
    $form->addFieldSet('search',array('legend'=>$PMDR->getLanguage('admin_languages_search')));
    $form->addField('id','select',array('label'=>$PMDR->getLanguage('admin_languages_language'),'fieldset'=>'search','value'=>$_GET['id'],'options'=>$db->GetAssoc("SELECT languageid, title FROM ".T_LANGUAGES)));
    $form->addField('keyword','text',array('label'=>$PMDR->getLanguage('admin_languages_words'),'fieldset'=>'search','help'=>$PMDR->getLanguage('admin_languages_words_help')));
    $keyword_options = array('AND'=>$PMDR->getLanguage('admin_languages_and'),'OR'=>$PMDR->getLanguage('admin_languages_or'),'EXACT'=>$PMDR->getLanguage('admin_languages_exact'));
    $form->addField('keyword_options','radio',array('label'=>$PMDR->getLanguage('admin_languages_search_type'),'fieldset'=>'search','value'=>'AND','options'=>$keyword_options,'help'=>$PMDR->getLanguage('admin_languages_search_type_help')));
    $filter_options = array('phrase'=>$PMDR->getLanguage('admin_languages_phrase'),'variable'=>$PMDR->getLanguage('admin_languages_variable'),'section'=>$PMDR->getLanguage('admin_languages_section'));
    $form->addField('filter','checkbox',array('label'=>$PMDR->getLanguage('admin_languages_search_in'),'fieldset'=>'search','value'=>$_GET['filter'],'options'=>$filter_options,'help'=>$PMDR->getLanguage('admin_languages_search_in_help')));
    $options = array('case_sensitive'=>$PMDR->getLanguage('admin_languages_case_sensitive'),'translated'=>$PMDR->getLanguage('admin_languages_translated'),'updated'=>$PMDR->getLanguage('admin_languages_updated'));
    $form->addField('options','checkbox',array('label'=>$PMDR->getLanguage('admin_languages_options'),'fieldset'=>'search','value'=>$_GET['options'],'options'=>$options,'help'=>$PMDR->getLanguage('admin_languages_options_help')));
    $form->addValidator('filter',new Validate_NonEmpty());
    $form->addField('search','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->loadValues($_GET);

    $form_edit = $PMDR->getNew('Form');
    $form_edit->addField('edit_phrases','submit',array('label'=>'Submit Phrase Changes','fieldset'=>'submit'));

    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('master_phraseid',$PMDR->getLanguage('admin_languages_id'),array('style'=>'width: 20px'));
    $table_list->addColumn('master_section',$PMDR->getLanguage('admin_languages_section'),array('style'=>'width: 20px'));
    $table_list->addColumn('master_variablename',$PMDR->getLanguage('admin_languages_variable'),array('style'=>'width: 20px'));
    $table_list->addColumn('content',$PMDR->getLanguage('admin_languages_phrase'),false,false,'width: 100%');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("
    SELECT SQL_CALC_FOUND_ROWS master.phraseid as master_phraseid, master.variablename as master_variablename, master.section as master_section, master.content as master_content, phrases.content
    FROM ".T_LANGUAGE_PHRASES." as master LEFT JOIN ".T_LANGUAGE_PHRASES." as phrases ON master.variablename=phrases.variablename AND master.section=phrases.section AND phrases.languageid=?
    WHERE master.languageid=-1 $translated_sql $updated_sql $keyword_sql
    GROUP BY master_variablename,master_section ORDER BY master.variablename LIMIT ".$paging->limit1.", ".$paging->limit2,array($_GET['id']));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $form_edit->addField('master_phrase_'.$record['master_section'].'_'.$record['master_variablename'],'textarea',array('label'=>$PMDR->getLanguage('admin_languages_content'),'fieldset'=>'phrase','value'=>$record['master_content'],'readonly'=>true,'style'=>'height: 100px'));
        $form_edit->addField('phrase-'.$record['master_section'].'-'.$record['master_variablename'],'textarea',array('label'=>$PMDR->getLanguage('admin_languages_content'),'fieldset'=>'phrase','value'=>$record['content'],'style'=>'height: 100px'));
        $records[$key]['content'] = '<span class="help-block">'.$PMDR->getLanguagE('admin_languages_default_phrase').'</span>';
        $records[$key]['content'] .= $form_edit->getFieldHTML('master_phrase_'.$record['master_section'].'_'.$record['master_variablename']);
        $records[$key]['content'] .= '<br />';
        $records[$key]['content'] .= '<span class="help-block">'.$PMDR->getLanguagE('admin_languages_custom_phrase').'</span>';
        $records[$key]['content'] .= $form_edit->getFieldHTML('phrase-'.$record['master_section'].'-'.$record['master_variablename']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&id='.$record['master_variablename'].'&section='.$record['master_section']));
        if($record['master_section'] == 'custom') {
            $records[$key]['manage'] = $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&id='.$record['master_variablename'].'&section='.$record['master_section']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);

    // If we edited a series of translations on the list view, handle the multi update here
    if($form_edit->wasSubmitted('edit_phrases')) {
        $data = $form_edit->loadValues();
        if(!$form_edit->validate()) {
            $PMDR->addMessage('error',$form_edit->parseErrorsForTemplate());
        } else {
            $phrase_array['phrases'] = array();
            foreach($data AS $key=>$value) {
                if(preg_match('/^phrase\-.*$/',$key)) {
                    $phrase_parts = explode('-',$key);
                    $phrase_array['phrases'][$phrase_parts[1]][$phrase_parts[2]] = $value;
                }
            }
            $PMDR->get('Cache')->deletePrefix('language_');
            $phrases->multiUpdate($phrase_array,$_GET['id']);
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_languages_phrases'))),'update');
            redirect_url(URL);
        }
    }

    $template_content->set('form_edit', $form_edit);
    $template_content->set('table_list',$table_list->render());
    $template_content->set('languages',$languages->getAll());
    $template_content->set('form', $form->toHTML());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_phrases_edit');

    $PMDR->get('Cache')->deletePrefix('language_');

    if($_GET['action'] == 'edit') {
        $form = $PMDR->get('Form');
        $form->addFieldSet('languages',array('legend'=>$PMDR->getLanguage('admin_languages')));

        $master = $db->GetRow("SELECT section, variablename, content FROM ".T_LANGUAGE_PHRASES." WHERE languageid=-1 AND variablename=? AND section=?",array($_GET['id'],$_GET['section']));
        $records = $db->GetAssoc("SELECT languageid, variablename, content FROM ".T_LANGUAGE_PHRASES." WHERE variablename=? AND section=? ORDER BY languageid",array($_GET['id'],$_GET['section']));

        $languages = $db->GetAssoc("SELECT languageid, title FROM ".T_LANGUAGES);
        foreach($languages as $languageid=>$language) {
            $form->addField('language'.$languageid,'textarea',array('label'=>$language.' Translation','fieldset'=>'languages','value'=>$records[$languageid]['content']));
        }

        $form->addField('edit_phrase','submit',array('label'=>'Submit','fieldset'=>'submit'));

        if($form->wasSubmitted('edit_phrase')) {
            $data = $form->loadValues();
            $data['language'] = array();
            foreach($languages AS $languageid=>$language) {
                $data['language'][$languageid] = $data['language'.$languageid];
            }
            $phrases->update($_GET['id'], $_GET['section'], $data['language']);
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_languages_phrases'))),'update');
            redirect();
        }

        $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_phrases_edit.tpl');
        $template_content->set('master',$master);
        $template_content->set('languages',$languages);
    } elseif($_GET['action'] == 'add') {
        $form = $PMDR->get('Form');
        $form->addFieldSet('phrase',array('legend'=>$PMDR->getLanguage('admin_languages_phrase')));
        $form->addField('variablename','text',array('label'=>$PMDR->getLanguage('admin_languages_variable'),'fieldset'=>'phrase'));
        $form->addField('phrase_content','textarea',array('label'=>$PMDR->getLanguage('admin_languages_content'),'fieldset'=>'phrase'));
        $form->addField('add_phrase','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
        $form->addValidator('variablename',new Validate_NonEmpty());
        $form->addValidator('phrase_content',new Validate_NonEmpty());

        if($form->wasSubmitted('add_phrase')) {
            $data = $form->loadValues();

            // We can not use the field name "content" as it conflicts with the CSS for #content so we convert it after submitting
            $data['content'] = $data['phrase_content'];
            unset($data['phrase_content']);

            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                $phrases->insert($data);
                // Redirect to where we can translate all of the languages
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['variablename'],$PMDR->getLanguage('admin_languages_phrases'))),'insert');
                redirect(array('action'=>'edit','id'=>$data['variablename'],'section'=>'custom'));
            }
        }
        $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_phrases_add.tpl');
    }
    $template_content->set('form', $form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_languages_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>