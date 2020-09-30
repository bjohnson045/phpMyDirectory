<?php
define('PMD_SECTION', 'members');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('user_blog','email_templates'));

if(!$PMDR->getConfig('blog_user_posts') OR !ADDON_BLOG) {
    redirect_url(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_blog_posts'));
$PMDR->set('meta_title',$PMDR->getLanguage('user_blog_posts'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_blog.php','text'=>$PMDR->getLanguage('user_blog_posts')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_blog.tpl');

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

if(isset($_GET['id'])) {
    $blog_post = $db->GetRow("SELECT * FROM ".T_BLOG." WHERE id=?",array($_GET['id']));
    if(!is_null($blog_post['listing_id'])) {
        $listing = $PMDR->get('Listings')->getRow($blog_post['listing_id']);
    }
    if($blog_post['user_id'] != $user['id']) {
        redirect_url(BASE_URL.MEMBERS_FOLDER);
    }
}

if(!isset($listing) AND isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
}

if(isset($listing)) {
    if($user['id'] != $listing['user_id']) {
        redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
    }
    $PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('blog_posts'));
}

if($_GET['action'] == 'delete') {
    if($PMDR->getConfig('blog_user_delete_posts')) {
        $PMDR->get('Email_Templates')->send('admin_blog_user_deleted',array('blog_id'=>$_GET['id']));
        $PMDR->get('Blog')->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_blog_post'))),'delete');
    }
    redirect();
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('user_blog_posts'));
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_blog_list.tpl'));
    $table_list->addColumn('title');
    $table_list->addColumn('date');
    $table_list->addColumn('status');
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $table_list->addSorting(array('title','date','status'));
    $paging = $PMDR->get('Paging');
    $where = array();
    if(isset($listing)) {
        $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_blog.php?action=add&listing_id='.$listing['id']);
        $where[] = 'listing_id = '.$PMDR->get('Cleaner')->clean_db($listing['id']);
    } else {
        $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_blog.php?action=add');
    }
    if(count($where)) {
        $where = 'AND '.implode(' AND ',$where);
    } else {
        $where = '';
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS b.* FROM ".T_BLOG." b WHERE user_id=? $where".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],"date ASC")." LIMIT ?,?",array($user['id'],$paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['url'] = $PMDR->get('Blog')->getURL($record['id'],$record['friendly_url']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    if($_GET['action'] == 'add') {
        $posts_limit = $db->GetOne("SELECT COUNT(*) FROM ".T_BLOG." WHERE user_id=? AND date > DATE_SUB(NOW(),INTERVAL ".$PMDR->getConfig('blog_user_posts_days_limit')." DAY)",array($user['id']));
        if($posts_limit > $PMDR->getConfig('blog_user_posts_limit')) {
            $PMDR->addMessage('error','You have exceeded your post limit of '.$PMDR->getConfig('blog_user_posts_limit').' posts within '.$PMDR->getConfig('blog_user_posts_days_limit').' days.');
            redirect_url(BASE_URL.MEMBERS_FOLDER);
        }
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG_CATEGORIES)) {
            $PMDR->addMessage('error','New blog posts are currently unavailable because no categories exist.  Please contact us.');
            redirect_url(BASE_URL.MEMBERS_FOLDER);
        }
    }

    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_blog_form.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('post');
    $form->addField('title','text',array('onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    $form->addField('friendly_url','text');
    $statuses = array(
        'active'=>$PMDR->getLanguage('active'),
        'suspended'=>$PMDR->getLanguage('suspended')
    );
    if($PMDR->getConfig('blog_user_posts_publish_date')) {
        $form->addField('date_publish','datetime',array('value'=>$PMDR->get('Dates_Local')->dateTimeNow()));
    }
    $form->addField('user_display','text');
    $form->addField('image','file',array('options'=>array('url_allow'=>true)));
    if($_GET['action'] == 'edit' AND $image = get_file_url(BLOG_PATH.intval($_GET['id']).'.*',true)) {
        $form->addField('image_current','custom',array('html'=>'<img src="'.$image.'">'));
        $form->addField('image_delete','checkbox',array('value'=>'0'));
    }
    $form->addField('categories','select_multiple',array('options'=>$db->GetAssoc("SELECT id, title FROM ".T_BLOG_CATEGORIES." ORDER BY title")));
    $form->addField('content_short','textarea');
    $form->addField('content','htmleditor');
    $form->addField('keywords','text');
    $form->addField('meta_title','text');
    $form->addField('meta_keywords','text');
    $form->addField('meta_description','textarea');
    $fields = $PMDR->get('Fields')->addToForm($form,'blog',array('fieldset'=>'post'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('categories',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('user_blog_posts_edit'));
        $values = $db->GetRow("SELECT * FROM ".T_BLOG." WHERE id=?",array($_GET['id']));
        $values['categories'] = $db->GetCol("SELECT category_id FROM ".T_BLOG_CATEGORIES_LOOKUP." WHERE blog_id=?",array($_GET['id']));
        $form->loadValues($values);
        unset($values);
    } else {
        $template_content->set('title',$PMDR->getLanguage('user_blog_posts_add'));
    }
    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if(isset($listing)) {
            $blog_posts_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_BLOG." WHERE listing_id=?",array($listing['id']));
            if($blog_posts_count >= $listing['blog_posts_limit'] AND $_GET['action'] != 'edit') {
                $form->addError($PMDR->getLanguage('user_blog_posts_limit_exceeded'));
            }
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                if($PMDR->getConfig('blog_user_posts_auto_approve')) {
                    $data['status'] = 'active';
                } else {
                    $data['status'] = 'pending';
                }
                $data['user_id'] = $user['id'];
                if(isset($listing)) {
                    $data['listing_id'] = $listing['id'];
                }
                $id = $PMDR->get('Blog')->insert($data);
                $PMDR->get('Email_Templates')->send('admin_blog_user_submitted',array('blog_id'=>$id));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_blog_post'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Blog')->update($data,$_GET['id']);
                $PMDR->get('Email_Templates')->send('admin_blog_user_edited',array('blog_id'=>$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_blog_post'))),'update');
            }
            if(!empty($listing)) {
                redirect(array('listing_id'=>$listing['id']));
            } else {
                redirect();
            }
        }
    }
    $template_content_form->set('form',$form);
    $template_content_form->set('fields',$fields);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>