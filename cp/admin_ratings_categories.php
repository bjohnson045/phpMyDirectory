<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_ratings','admin_reviews','admin_reviews_comments','admin_ratings_categories'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_ratings_view');

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_RATINGS_CATEGORIES." WHERE id=?",array($_GET['id']));
    $db->DropColumn(T_RATINGS_CATEGORIES,'category_'.$db->Clean($_GET['id'],false));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_ratings_categories_category'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->sortable(T_RATINGS_CATEGORIES,$PMDR->getLanguage('admin_ratings_categories_ordering'));
    $table_list->addColumn('title');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_RATINGS_CATEGORIES." ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],"ordering ASC")." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_ratings_categories'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information');
    $form->addField('title','text');
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_ratings_categories_edit'));
        $form->loadValues($db->GetRow("SELECT * FROM ".T_RATINGS_CATEGORIES." WHERE id=?",$_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_ratings_categories_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $db->Execute("INSERT INTO ".T_RATINGS_CATEGORIES." (title) VALUES (?)",array($data['title']));
                $db->AddColumn(T_RATINGS,'category_'.$db->Insert_ID(),'tinyint(1)',false,0,'unsigned');
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_ratings_categories_category'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $db->Execute("UPDATE ".T_RATINGS_CATEGORIES." SET title=? WHERE id=?",array($data['title'],$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_ratings_categories_category'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_reviews_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>