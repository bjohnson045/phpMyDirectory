<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_blog','admin_listings','admin_users'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_blog.tpl');

if(!empty($_GET['listing_id'])) {
    if(!$listing = $PMDR->get('Listings')->getRow($_GET['listing_id'])) {
        redirect();
    }
    $template_content->set('listing_header',$PMDR->get('Listing',$_GET['listing_id'])->getAdminHeader('blog_posts'));
    $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('blog_posts'));
} elseif(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('blog_posts'));
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Blog')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_blog_post'))),'delete');
    redirect_action();
}

if($_GET['action'] == 'approve') {
    $db->Execute("UPDATE ".T_BLOG." SET status='active' WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_blog_post'))),'update');
    redirect_action();
}

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $form_search = $PMDR->getNew('Form');
    $form_search->method = 'GET';
    $form_search->addFieldSet('blog_search',array('legend'=>$PMDR->getLanguage('admin_blog_search')));
    $status_options = array(
        'active'=>$PMDR->getLanguage('active'),
        'suspended'=>$PMDR->getLanguage('suspended')
    );
    $form_search->addField('status','select',array('first_option'=>'','value'=>$_GET['status'],'options'=>$status_options,'help'=>''));
    $form_search->addField('keywords','text',array('value'=>$_GET['keywords'],'help'=>''));
    $form_search->addField('category','select',array('first_option'=>'','value'=>$_GET['category'],'options'=>$db->GetAssoc("SELECT id, title FROM ".T_BLOG_CATEGORIES." ORDER BY title")));
    $form_search->addField('published','checkbox',array('value'=>$_GET['published']));
    $form_search->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $template_content->set('form_search',$form_search);

    $template_content->set('title',$PMDR->getLanguage('admin_blog_posts'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title');
    $table_list->addColumn('date');
    if(empty($_GET['user_id'])) {
        $table_list->addColumn('user_id',$PMDR->getLanguage('admin_blog_user'),true);
    }
    if(empty($_GET['listing_id'])) {
        $table_list->addColumn('listing_id');
    }
    $table_list->addColumn('status');
    $table_list->addColumn('manage');
    $table_list->addSorting(array('title','date','status'));
    $paging = $PMDR->get('Paging');
    $where = array();
    if(!empty($_GET['status'])) {
        $where[] = "b.status = ".$PMDR->get('Cleaner')->clean_db($_GET['status']);
    }
    if(!empty($_GET['keywords'])) {
        $where[] = "MATCH(b.title,b.keywords,b.content_short) AGAINST (".$PMDR->get('Cleaner')->clean_db($_GET['keywords']).")";
    }
    if(!empty($_GET['published'])) {
        $where[] = "date_publish IS NULL";
    }
    if(!empty($_GET['category'])) {
        $category_join = 'INNER JOIN '.T_BLOG_CATEGORIES_LOOKUP.' bcl ON b.id=bcl.blog_id';
        $where[] = "bcl.category_id = ".$PMDR->get('Cleaner')->clean_db($_GET['category']);
    }
    if(isset($_GET['listing_id'])) {
        $where[] = 'listing_id = '.$PMDR->get('Cleaner')->clean_db($_GET['listing_id']);
    }
    if(isset($_GET['user_id'])) {
        $where[] = 'b.user_id = '.$PMDR->get('Cleaner')->clean_db($_GET['user_id']);
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }
    $records = $db->GetAll("
        SELECT
            SQL_CALC_FOUND_ROWS b.*,
            COALESCE(NULLIF(TRIM(CONCAT(u.user_first_name,' ',u.user_last_name)),''),u.login) AS user_display,
            l.title AS listing_title
        FROM
            ".T_BLOG." b
            LEFT JOIN ".T_LISTINGS." l ON b.listing_id=l.id
            INNER JOIN ".T_USERS." u ON b.user_id=u.id $category_join $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],"date ASC")." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = '<a href="admin_blog.php?action=edit&id='.$record['id'].'">'.$record['title'].'</a>';
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        if(!empty($_GET['user_id'])) {
            $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">'.$record['user_display'].'</a>';
        }
        $records[$key]['status'] = '<span class="label label-'.$record['status'].'">'.$PMDR->getLanguage($record['status']).'</a>';
        if(!is_null($record['listing_id'])) {
            $records[$key]['listing_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_listings.php?id='.$record['listing_id'].'&action=edit">'.$record['listing_title'].'</a>';
        } else {
            $records[$key]['listing_id'] = '-';
        }
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>rebuild_url(array('action'=>'edit','id'=>$record['id']))));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('target'=>'_blank','href'=>$PMDR->get('Blog')->getURL($record['id'],$record['friendly_url'])));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= '<a href="'.rebuild_url(array('action'=>'approve','id'=>$record['id'])).'"><i class="text-success fa fa-check"></i></a> ';
        }
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    if($_GET['action'] == 'add') {
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG_CATEGORIES)) {
            $PMDR->addMessage('error','You must first add a blog post category before adding a blog post.');
            redirect_url(BASE_URL_ADMIN.'/admin_blog_categories.php?action=add');
        }
    }
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('post');
    $form->addField('title','text',array('onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    $form->addField('friendly_url','text');
    $statuses = array(
        'active'=>$PMDR->getLanguage('active'),
        'pending'=>$PMDR->getLanguage('pending'),
        'suspended'=>$PMDR->getLanguage('suspended')
    );
    $form->addField('status','select',array('options'=>$statuses));
    $form->addField('date_publish','datetime',array('value'=>$PMDR->get('Dates_Local')->dateTimeNow()));
    $form->addField('user_display','text');
    // aDD BLOG  resizing settings
    $form->addField('image','file',array('options'=>array('url_allow'=>true)));
    if($_GET['action'] == 'edit' AND $image = get_file_url(BLOG_PATH.$_GET['id'].'.*',true)) {
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
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('categories',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_blog_posts_edit'));
        $values = $db->GetRow("SELECT * FROM ".T_BLOG." WHERE id=?",array($_GET['id']));
        $values['categories'] = $db->GetCol("SELECT category_id FROM ".T_BLOG_CATEGORIES_LOOKUP." WHERE blog_id=?",array($_GET['id']));
        $form->loadValues($values);
        unset($values);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_blog_posts_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                if(isset($_GET['listing_id'])) {
                    $data['listing_id'] = intval($_GET['listing_id']);
                }
                $PMDR->get('Blog')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_blog_post'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Blog')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_blog_post'))),'update');
            }
            redirect_action();
        }
    }
    $template_content->set('content',$form->toHTML());
}

if(!isset($_GET['listing_id']) AND !isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_blog_menu.tpl');
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>