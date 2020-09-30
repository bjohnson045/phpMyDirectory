<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_faq'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_faq_categories_view');

$faq_categories = $PMDR->get('FAQ_Categories');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_faq_categories_delete');
    $faq_categories->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_faq'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->sortable(T_FAQ_CATEGORIES,$PMDR->getLanguage('admin_faq_categories_ordering'));
    $table_list->addColumn('title');
    $table_list->addColumn('active');
    $table_list->addColumn('manage');
    $table_list->setTotalResults($faq_categories->getCount());
    $records = $faq_categories->GetRows(array(),array('ordering'=>'ASC'),$table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('question',array('title'=>$PMDR->getLanguage('admin_faq_questions'),'href'=>'admin_faq_questions.php?category_id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_faq_categories'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_faq_categories_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information');
    $form->addField('title','text');
    $form->addField('active','checkbox',array('value'=>1));
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_faq_categories_edit'));
        $form->loadValues($faq_categories->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_faq_categories_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Cache')->delete('faq');
            if($_GET['action']=='add') {
                $faq_categories->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_faq'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $faq_categories->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_faq'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_faq_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>