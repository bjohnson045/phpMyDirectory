<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_jobs'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if($_GET['action'] == 'delete') {
    if($PMDR->get('Jobs')->deleteCategory($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_jobs_category'))),'delete');
    }
    redirect();
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_jobs_categories'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title');
    $table_list->addColumn('manage');
    $table_list->addSorting('title');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_JOBS_CATEGORIES." ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'title ASC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
    $records[$key]['title'] = '<a href="admin_jobs_categories.php?action=edit&id='.$record['id'].'">'.$record['title'].'</a>';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_JOBS_CATEGORIES_LOOKUP." WHERE category_id=?",array($record['id']))) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addField('title','text',array('onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    $form->addField('friendly_url','text');
    $form->addField('keywords','textarea');
    $form->addField('meta_title','text');
    $form->addField('meta_keywords','text');
    $form->addField('meta_description','textarea');
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_jobs_categories_edit'));
        $form->loadValues($db->GetRow("SELECT * FROM ".T_JOBS_CATEGORIES." WHERE id=?",array($_GET['id'])));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_jobs_categories_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $data['friendly_url'] = Strings::rewrite($data['friendly_url']);
            if($_GET['action']=='add') {
                $PMDR->get('Jobs')->insertCategory($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_jobs_category'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Jobs')->updateCategory($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_jobs_category'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_jobs_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>