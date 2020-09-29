<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_ratings','admin_listings','admin_reviews','admin_reviews_comments','admin_users','admin_ratings_categories'));

$PMDR->get('Authentication')->checkPermission('admin_ratings_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_ratings_delete');
    $PMDR->get('Ratings')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_ratings'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    $template_content->set('listing_header',$PMDR->get('Listing',$_GET['listing_id'])->getAdminHeader('ratings'));
    $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('ratings'));
} elseif(isset($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('ratings'));
}

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_ratings_listing_id'));
    }
    $table_list->addColumn('review_id',$PMDR->getLanguage('admin_ratings_review_id'));
    $table_list->addColumn('rating',$PMDR->getLanguage('admin_ratings_rating'));
    $table_list->addColumn('ip_address',$PMDR->getLanguage('admin_ratings_ip_address'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    if(isset($_GET['listing_id']) AND intval($_GET['listing_id'])) {
        $where[] = 'r.listing_id='.$db->Clean($listing['id']);
    }
    if(isset($_GET['user_id']) AND intval($_GET['user_id'])) {
        $where[] = 'r.user_id='.$db->Clean($_GET['user_id']);
    }
    $paging = $PMDR->get('Paging');
    $average_string = 'r.rating';
    $ratings_categories = $db->GetCol("SELECT id FROM ".T_RATINGS_CATEGORIES);
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS r.*, rv.id AS review_id, rv.status AS review_status FROM ".T_RATINGS." r LEFT JOIN ".T_REVIEWS." rv ON rv.rating_id=r.id".(count($where) ? ' WHERE '.implode(' AND ',$where) : '')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    $content_javascript = '
    <script type="text/javascript">
    $(document).ready(function(){';
    foreach($records as $key=>$record) {
        $records[$key]['review_id'] = ($record['review_id'] == 0) ? '-' : '<a href="admin_reviews.php?action=edit&id='.$record['review_id'].'">'.$record['review_id'].'</a>';
        if(!is_null($record['review_id']) AND $record['review_status'] != 'active') {
            $records[$key]['review_id'] .= ' <i id="review_status_icon'.$key.'" class="glyphicon glyphicon-exclamation-sign"></i>';
            $content_javascript .= "tooltip('review_status_icon".$key."', 'The review this rating is related to is currently pending and needs to be <a href=\"admin_reviews.php?action=edit&id=".$record['review_id']."\">approved</a> before this rating becomes active.');";
        }
        $total = $record['rating'];
        foreach($ratings_categories AS $category_id) {
            $total += $record['category_'.$category_id];
        }
        $average = $total / (1 + count($ratings_categories));
        $records[$key]['rating'] = $PMDR->get('Ratings')->printRatingStatic($average);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&id='.$record['id'].'&listing_id='.$record['listing_id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&id='.$record['id'].'&listing_id='.$record['listing_id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_ratings'));
    $content_javascript .= '});
    </script>';
    $content = $table_list->render()->render();
    $content .= $content_javascript;
    $template_content->set('content',$content);
} else {
    $PMDR->get('Authentication')->checkPermission('admin_ratings_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_ratings_rating')));
    if($_GET['action'] == 'add' AND !isset($_GET['listing_id'])) {
        $form->addField('listing_id','select_window',array('label'=>$PMDR->getLanguage('admin_ratings_listing_id'),'fieldset'=>'information','options'=>'select_listing'));
        $form->addValidator('listing_id',new Validate_NonEmpty());
    } else {
        $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('admin_ratings_listing_id'),'fieldset'=>'information','value'=>$_GET['listing_id']));
    }
    $form->addField('rating','stars',array('label'=>$PMDR->getLanguage(($ratings_categories ? 'admin_ratings_overall' : 'admin_ratings_rating')),'fieldset'=>'information','value'=>0));
    $ratings_categories = $db->GetAll("SELECT id, title FROM ".T_RATINGS_CATEGORIES." ORDER BY ordering, title");
    foreach($ratings_categories AS $category) {
        $form->addField('category_'.$category['id'],'stars',array('label'=>$category['title'].' '.$PMDR->getLanguage('admin_ratings_rating'),'fieldset'=>'information','value'=>0));
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_ratings_edit'));
        $form->loadValues($PMDR->get('Ratings')->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_ratings_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Ratings')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['rating'],$PMDR->getLanguage('admin_ratings'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Ratings')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['rating'],$PMDR->getLanguage('admin_ratings'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
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