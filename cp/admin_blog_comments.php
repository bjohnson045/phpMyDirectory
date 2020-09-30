<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_blog','email_templates'));

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_BLOG_COMMENTS." WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_blog_comment'))),'delete');
    redirect();
}

if($_GET['action'] == 'approve') {
    $PMDR->get('Blog')->approveComment($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_blog_post'))),'update');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_blog_comments'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_blog_id'),true);
    $table_list->addColumn('user_id',$PMDR->getLanguage('admin_blog_user'),true);
    $table_list->addColumn('blog_id',$PMDR->getLanguage('admin_blog_post'),true);
    $table_list->addColumn('date',$PMDR->getLanguage('admin_blog_date'),true);
    $table_list->addColumn('status',$PMDR->getLanguage('admin_blog_status'),true);
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $where = array();
    if(!empty($_GET['status'])) {
        $where[] = "bc.status = ".$PMDR->get('Cleaner')->clean_db($_GET['status']);
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }

    $records = $db->GetAll("
    SELECT blog_comments.*, u.user_first_name, u.user_last_name, u.login, b.title
    FROM
        (SELECT bc.* FROM ".T_BLOG_COMMENTS." bc $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date ASC')." LIMIT ".$paging->limit1.",".$paging->limit2.") AS blog_comments
        INNER JOIN ".T_BLOG." b ON blog_comments.blog_id=b.id
        LEFT JOIN ".T_USERS." u ON u.id=blog_comments.user_id");
    $paging->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_BLOG_COMMENTS." bc $where"));
    foreach($records as $key=>$record) {
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['blog_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_blog.php?action=edit&id='.$record['blog_id'].'">'.$record['title'].'</a> (ID:'.$record['blog_id'].')';
        if(is_null($record['user_id'])) {
            $records[$key]['user_id'] = $record['name'];
        } else {
            $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">'.$db->GetOne("SELECT COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login) AS user FROM ".T_USERS." WHERE id=?",array($record['user_id'])).'</a>';
        }
        $records[$key]['status'] = $PMDR->getLanguage($record['status']);

        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('target'=>'_blank','href'=>BASE_URL.'/blog_post.php?id='.$record['blog_id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('checkmark',array('href'=>URL_NOQUERY.'?action=approve&id='.$record['id']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_blog_comment')));
    $statuses = array(
        'active'=>$PMDR->getLanguage('active'),
        'pending'=>$PMDR->getLanguage('pending')
    );
    $form->addField('status','select',array('label'=>$PMDR->getLanguage('admin_blog_status'),'fieldset'=>'information','options'=>$statuses));
    $form->addField('comment','textarea',array('label'=>$PMDR->getLanguage('admin_blog_comments'),'fieldset'=>'information'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('comment',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_blog_comments_edit'));
        $edit_comment = $db->GetRow("SELECT * FROM ".T_BLOG_COMMENTS." WHERE id=?",array($_GET['id']));
        $form->loadValues($edit_comment);
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action'] == 'edit') {
                if($edit_comment['status'] == 'pending' AND $data['status'] == 'active') {
                    $PMDR->get('Blog')->approveComment($_GET['id']);
                }
                $db->Execute("UPDATE ".T_BLOG_COMMENTS." SET status=?, comment=? WHERE id=?",array($data['status'],$data['comment'],$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_blog_post'))),'update');
            }
            redirect();
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_blog_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>