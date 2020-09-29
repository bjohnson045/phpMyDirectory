<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_reviews','admin_listings','admin_ratings','admin_reviews_comments','admin_users','admin_ratings_categories','email_templates'));

$PMDR->get('Authentication')->checkPermission('admin_reviews_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_reviews_delete');
    $PMDR->get('Reviews')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_reviews'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if($_GET['action'] == 'approve') {
    $PMDR->get('Reviews')->approve($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_reviews_approved',$_GET['id']));
    redirect(array('listing_id'=>$_GET['listing_id']));
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!empty($_GET['listing_id'])) {
    if(!$listing = $PMDR->get('Listings')->getRow($_GET['listing_id'])) {
        redirect();
    }
    $template_content->set('listing_header',$PMDR->get('Listing',$_GET['listing_id'])->getAdminHeader('reviews'));
    $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
} elseif(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('reviews'));
}

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_reviews_listing'));
    }
    if(empty($_GET['user_id'])) {
        $table_list->addColumn('user_id');
    }
    $table_list->addColumn('status');
    $table_list->addColumn('date');
    $table_list->addColumn('title');
    $table_list->addColumn('rating');
    $table_list->addColumn('comment_count',$PMDR->getLanguage('admin_reviews_comments'));
    $table_list->addSorting(array('listing_id','user_id','date','title','comment_count','status'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));

    if(isset($_GET['id'])) {
        $where[] = 'r.id='.$db->Clean($_GET['id']);
    }
    if(isset($_GET['listing_id'])) {
        $where[] = 'r.listing_id='.$db->Clean($listing['id']);
    }
    if(isset($_GET['status'])) {
        $where[] = 'r.status='.$db->Clean($_GET['status']);
    }
    if(isset($_GET['user_id'])) {
        $where[] = 'r.user_id='.$db->Clean($_GET['user_id']);
    }
    if(!empty($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    }

    if($ratings_categories = $db->GetCol("SELECT id FROM ".T_RATINGS_CATEGORIES)) {
        $categories_sql = 'ra.category_'.implode(',ra.category_',$ratings_categories).',';
    }

    $records = $db->GetAll("
    SELECT reviews.*, u.user_first_name, u.user_last_name, u.login, ra.rating, l.user_id AS listing_user_id, $categories_sql COUNT(rc.id) AS comments, MAX(rc.user_id = l.user_id) AS owner_response
    FROM
        (SELECT r.* FROM ".T_REVIEWS." r $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC')." LIMIT ?,?) AS reviews
        LEFT JOIN ".T_RATINGS." ra ON reviews.rating_id=ra.id
        LEFT JOIN ".T_REVIEWS_COMMENTS." rc ON reviews.id=rc.review_id
        LEFT JOIN ".T_USERS." u ON u.id = reviews.user_id
        LEFT JOIN ".T_LISTINGS." l ON l.id=reviews.listing_id
    GROUP BY reviews.id
    ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC'),
    array($table_list->paging->limit1,$table_list->paging->limit2));

    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS." r $where"));

    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['status'] = $PMDR->get('HTML')->icon((($record['status'] == 'active') ? '1' : '0'));
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $total = $record['rating'];
        foreach($ratings_categories AS $category_id) {
            $total += $record['category_'.$category_id];
        }
        $average = $total / (1 + count($ratings_categories));
        $records[$key]['rating'] = $PMDR->get('Ratings')->printRatingStatic($average);
        if($record['comments']) {
            $records[$key]['comment_count'] = '<a href="admin_reviews_comments.php?review_id='.$record['id'].'">'.$record['comments'].'</a>';
            if($record['owner_response']) {
                $records[$key]['comment_count'] .= ' <span class="glyphicon glyphicon-user" title="'.$PMDR->getLanguage('admin_reviews_listing_owner_responded').'""></span>';
            }
        } else {
            $records[$key]['comment_count'] = 0;
        }
        if(empty($_GET['user_id'])) {
            if($record['user_id'] == NULL) {
                $records[$key]['user_id'] = '-';
            } else {
                $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
                $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
                $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
            }
        }
        if(empty($listing)) {
            $listing = $db->GetRow("SELECT id, friendly_url, title FROM ".T_LISTINGS." WHERE id=?",array($record['listing_id']));
            $records[$key]['listing_id'] = '<a href="'.$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']).'">'.$listing['title'].'</a> (ID: '.$listing['id'].')';
            unset($listing);
        }

        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&id='.$record['id'].'&listing_id='.$record['listing_id']));
        if($record['status'] == 'active') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>BASE_URL.'/listing_reviews.php?review_id='.$record['id']));
        }
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&id='.$record['id'].'&listing_id='.$record['listing_id']));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('checkmark',array('label'=>$PMDR->getLanguage('admin_reviews_approve'),'href'=>URL_NOQUERY.'?action=approve&id='.$record['id'].'&from='.urlencode_url(URL)));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_reviews'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_reviews_edit');
    // Create form
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_reviews_review')));

    if(isset($_GET['listing_id'])) {
        $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('admin_reviews_listing_id'),'fieldset'=>'information','value'=>$_GET['listing_id']));
    } else {
        $form->addField('listing_id','select_window',array('label'=>$PMDR->getLanguage('admin_reviews_listing_id'),'fieldset'=>'information','options'=>'select_listing'));
    }
    if($PMDR->getConfig('user_select') == 'select_window') {
        $form->addField('user_id','select_window',array('label'=>$PMDR->getLanguage('admin_reviews_user_id'),'fieldset'=>'information','icon'=>'users_search','options'=>'select_user'));
    } else {
        $form->addField('user_id','select',array('label'=>$PMDR->getLanguage('admin_reviews_user_id'),'fieldset'=>'information','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
    }
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_reviews_name'),'fieldset'=>'information'));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_reviews_title'),'fieldset'=>'information'));
    $form->addField('review','textarea',array('label'=>$PMDR->getLanguage('admin_reviews_review'),'fieldset'=>'information'));
    $form->addField('rating','stars',array('label'=>$PMDR->getLanguage(($ratings_categories ? 'admin_ratings_overall' : 'admin_ratings_rating')),'fieldset'=>'information','value'=>0));
    $ratings_categories = $db->GetAll("SELECT id, title FROM ".T_RATINGS_CATEGORIES." ORDER BY ordering, title");
    foreach($ratings_categories AS $category) {
        $form->addField('category_'.$category['id'],'stars',array('label'=>$category['title'].' '.$PMDR->getLanguage('admin_reviews_rating'),'fieldset'=>'information','value'=>0));
    }
    $form->addField('status','select',array('label'=>$PMDR->getLanguage('admin_reviews_status'),'fieldset'=>'information','options'=>array('active'=>'Active','pending'=>'Pending')));
    $custom_fields = $PMDR->get('Fields')->addToForm($form,'reviews',array('fieldset'=>'information'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('user_id',new Validate_NonEmpty());
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('review',new Validate_NonEmpty());
    $form->addValidator('rating',new Validate_NonEmpty());

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_reviews_edit'));
        $review = $PMDR->get('Reviews')->getRow($_GET['id']);
        if(is_null($review['user_id'])) {
            $form->deleteField('user_id');
        } else {
            $form->deleteField('name');
        }
        $form->loadValues($review);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_reviews_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if($_GET['action']=='add') {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS." WHERE user_id=? AND listing_id=?",array($data['user_id'],$data['listing_id']))) {
                $form->addError($PMDR->getLanguage('admin_reviews_add_error'),'user_id');
            }
        }

        if(!empty($data['user_id'])) {
            $form->removeValidator('name');
            $data['name'] = '';
        } elseif(trim($data['name']) != '') {
            $form->removeValidator('user_id');
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                if($data['rating_id'] = $PMDR->get('DB')->GetOne("SELECT id FROM ".T_RATINGS." WHERE listing_id=? AND user_id=? LIMIT 1",array($data['listing_id'], $data['user_id']))) {
                    $PMDR->get('Ratings')->update(array('rating'=>$data['rating'],'listing_id'=>$data['listing_id'],'ip_address'=>get_ip_address()), $data['rating_id']);
                } else {
                    $data['rating_id'] = $PMDR->get('Ratings')->insert(array('rating'=>$data['rating'],'listing_id'=>$data['listing_id'],'user_id'=>$data['user_id'],'ip_address'=>get_ip_address()));
                }
                $data['id'] = $PMDR->get('Reviews')->insert($data);

                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($form->getFieldValue('title'),$PMDR->getLanguage('admin_reviews'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $data['rating_id'] = $review['rating_id'];
                $PMDR->get('Reviews')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($form->getFieldValue('title'),$PMDR->getLanguage('admin_reviews'))),'update');
            }
            if(isset($_GET['listing_id'])) {
                redirect(null,array('listing_id'=>$_GET['listing_id']));
            } elseif(!empty($_GET['user_id'])) {
                redirect(null,array('user_id'=>$_GET['user_id']));
            } else {
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
if(!isset($_GET['listing_id']) AND !isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_reviews_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>