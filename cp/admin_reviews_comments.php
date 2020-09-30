<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_reviews_comments','admin_reviews','admin_ratings','admin_ratings_categories'));

$PMDR->get('Authentication')->checkPermission('admin_reviews_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_reviews_delete');
    $PMDR->get('Reviews_Comments')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_reviews_comments_comment'))),'delete');
    redirect(array('review_id'=>$_GET['review_id']));
}

if($_GET['action'] == 'approve') {
    $PMDR->get('Reviews_Comments')->approve($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_approved',array($_GET['id'],$PMDR->getLanguage('admin_reviews_comments_comment'))),'updated');
    redirect(array('review_id'=>$_GET['review_id']));
}

if(isset($_POST['table_list_submit'])) {
    if(isset($_POST['table_list_checkboxes']) AND count($_POST['table_list_checkboxes'])) {
        foreach($_POST['table_list_checkboxes'] AS $id) {
            if($_POST['action'] == 'approve') {
                $PMDR->get('Reviews_Comments')->approve($id);
            } elseif($_POST['action'] == 'unapprove') {
                $PMDR->get('Reviews_Comments')->unapprove($id);
            } elseif($_POST['action'] == 'delete') {
                $PMDR->get('Reviews_Comments')->delete($id);
            }
        }
    }
    $PMDR->addMessage('success','Comments updated');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $checkbox_options =
    array(
        ''=>'',
        'approve'=>$PMDR->getLanguage('approve'),
        'unapprove'=>$PMDR->getLanguage('unapprove'),
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$checkbox_options)));
    $table_list->addColumn('review_id');
    $table_list->addColumn('user_id');
    $table_list->addColumn('date');
    $table_list->addColumn('comment');
    $table_list->addColumn('status');
    $table_list->addColumn('manage');
    $table_list->addSorting(array('review_id','listing_id','user_id','date','status'));
    $paging = $PMDR->get('Paging');
    if(isset($_GET['review_id'])) {
        $where[] = 'c.review_id='.$db->Clean($_GET['review_id']);
    }
    if(isset($_GET['status'])) {
        $where[] = 'c.status='.$db->Clean($_GET['status']);
    }
    if(!empty($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    }
    $records = $db->GetAll("
    SELECT SQL_CALC_FOUND_ROWS comments.*, l.user_id AS listing_user_id, u.user_first_name, u.user_last_name, u.login
    FROM
         (SELECT c.* FROM ".T_REVIEWS_COMMENTS." c $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC')." LIMIT ?,?) AS comments
         LEFT JOIN ".T_USERS." u ON comments.user_id=u.id
         LEFT JOIN ".T_REVIEWS." r ON comments.user_id=r.id
         LEFT JOIN ".T_LISTINGS." l ON l.id=r.listing_id
    ",array($paging->limit1,$paging->limit2));

    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
        $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
        $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
        if($record['listing_user_id'] == $record['user_id']) {
            $records[$key]['user_id'] .= ' <span class="label label-info">'.$PMDR->getLanguage('admin_reviews_listing_owner').'</span>';
        }
        $records[$key]['status'] = $PMDR->get('HTML')->icon((($record['status'] == 'active') ? '1' : '0'));
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['review_id'] = '<a href="admin_reviews.php?id='.$record['review_id'].'">'.$record['review_id'].'</a>';
        if($record['user_id'] == NULL) {
            $records[$key]['user_id'] = '-';
        }
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>BASE_URL.'/listing_reviews.php?review_id='.$record['review_id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('checkmark',array('label'=>$PMDR->getLanguage('admin_reviews_approve'),'href'=>URL_NOQUERY.'?action=approve&id='.$record['id']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_reviews_comments'));
    $template_content->set('content',$table_list->render());
} else {
    if(!$record = $db->GetRow("SELECT id, comment, status FROM ".T_REVIEWS_COMMENTS." WHERE id=?",array($_GET['id']))) {
        redirect();
    }

    $PMDR->get('Authentication')->checkPermission('admin_reviews_edit');

    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_reviews_comments_comment')));
    $form->addField('comment','textarea',array('fieldset'=>'information'));
    $form->addField('status','select',array('fieldset'=>'information','options'=>array('active'=>'Active','pending'=>'Pending')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('comment',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_reviews_comments_edit'));
        $form->loadValues($record);
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action'] == 'edit') {
                $db->Execute("UPDATE ".T_REVIEWS_COMMENTS." SET comment=?, status=? WHERE id=?",array($data['comment'],$data['status'],$record['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($record['id'],$PMDR->getLanguage('admin_reviews_comments_comment'))),'update');
                redirect(array('review_id'=>$_GET['review_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_reviews_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>