<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_faq'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_faq_questions_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_faq_questions_delete');
    $PMDR->get('FAQ_Questions')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_faq'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('category_id',null,sort,true);
    $table_list->addColumn('question');
    $table_list->addColumn('answer');
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_faq_questions_ordering').' <a href="" onclick="updateOrdering(\''.T_FAQ_QUESTIONS.'\',\'table_list_form\'); return false;" title="'.$PMDR->getLanguage('admin_update').'"><i class="glyphicon glyphicon-edit"></i></a>');
    $table_list->addColumn('active');
    $table_list->addColumn('manage');
    $table_list->setTotalResults($PMDR->get('FAQ_Questions')->getCount());
    $where = array();
    if(isset($_GET['category_id'])) {
        $where[] = 'category_id='.$PMDR->get('Cleaner')->clean_db($_GET['category_id']);
    }
    if(!empty($where)) {
        $where_sql = 'WHERE '.implode(' AND ',$where);
    }
    unset($where);
    $records = $db->GetAll("SELECT fq.*, fc.title AS category_title FROM ".T_FAQ_QUESTIONS." fq INNER JOIN ".T_FAQ_CATEGORIES." fc ON fq.category_id=fc.id $where_sql ORDER BY fq.category_id ASC, ordering ASC LIMIT ".$table_list->page_data['limit1'].", ".$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['ordering'] = '<input class="form-control input-xs" id="ordering_'.$record['id'].'" style="width: 43px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['category_id'] = $record['category_title'];
        $record['answer'] = strip_tags($record['answer']);
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_faq'));
    $template_content->set('content',$table_list->render());
} else {
    if($_GET['action'] == 'add') {
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_FAQ_CATEGORIES)) {
            $PMDR->addMessage('error','You must first add a FAQ category before adding a question.');
            redirect_url(BASE_URL_ADMIN.'/admin_faq_categories.php?action=add');
        }
    }
    $PMDR->get('Authentication')->checkPermission('admin_faq_questions_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information');
    $categories = $db->GetAssoc("SELECT id, title FROM ".T_FAQ_CATEGORIES." ORDER BY ordering DESC");
    $form->addField('category_id','select',array('value'=>'Select Category','options'=>$categories));
    $form->addField('question','text');
    $form->addField('answer','htmleditor');
    $form->addField('ordering','text');
    $form->addField('active','checkbox',array('value'=>1));
    $form->addField('submit','submit');

    $form->addValidator('question',new Validate_NonEmpty());
    $form->addValidator('answer',new Validate_NonEmpty());
    $form->addValidator('category_id',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_faq_questions_edit'));
        $form->loadValues($PMDR->get('FAQ_Questions')->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_faq_questions_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Cache')->delete('faq');
            if($_GET['action']=='add') {
                $PMDR->get('FAQ_Questions')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_faq'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('FAQ_Questions')->update($data, $_GET['id']);
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