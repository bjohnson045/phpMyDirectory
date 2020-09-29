<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_listings','admin_listings_claims','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_listings_claims_view');

/** @var Listings */
$listings = $PMDR->get('Listings');
/** @var Listings_Claims */
$listings_claims = $PMDR->get('Listings_Claims');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_listings_claims_edit');
    $PMDR->get('Email_Templates')->send('listing_claim_denied',array('to'=>$_GET['user_id'],'variables'=>array('claim_id'=>$_GET['id']),'listing_id'=>$_GET['listing_id']));
    $listings_claims->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_listings_claims'))),'delete');
    redirect();
}

if($_GET['action'] == 'approve') {
    $PMDR->get('Authentication')->checkPermission('admin_listings_claims_edit');
    if(!isset($_GET['confirmed']) AND $db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS_CLAIMS." WHERE listing_id=? AND id!=?",array($_GET['listing_id'],$_GET['id']))) {
        $PMDR->addMessage('notice','There are multiple claims for this listing.  Please review each claim before approving one of them.  Once one claim is approved, all other claims for that listing will be removed.<br>You may first remove invalid claims or <a href="'.URL.'&confirmed=true">click here to confirm this claim</a>.');
    } else {
        $PMDR->get('Email_Templates')->send('listing_claim_approved',array('to'=>$_GET['user_id'],'variables'=>array('claim_id'=>$_GET['id']),'listing_id'=>$_GET['listing_id']));
        $PMDR->get('Listings_claims')->claim($_GET['id'],$_GET['user_id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_listings_claims_approved'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_listings_claims_listing_id'));
    $table_list->addColumn('user_id',$PMDR->getLanguage('admin_listings_claims_user_id'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_listings_claims_date'));
    $table_list->addColumn('comments',$PMDR->getLanguage('admin_listings_comment'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($listings_claims->getCount());
    $records = $db->GetAll("
        SELECT c.*, l.title, l.friendly_url, u.user_first_name, u.user_last_name, u.login
        FROM
            ".T_LISTINGS_CLAIMS." c
                INNER JOIN ".T_LISTINGS." l ON c.listing_id = l.id
                INNER JOIN ".T_USERS." u ON u.id=c.user_id
        ORDER BY date ASC
        LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']
    );
    foreach($records as $key=>$record) {
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['listing_id'] = '<a href="'.$listings->getURL($record['listing_id'],$record['friendly_url']).'">'.$record['title'].'</a>';
        $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
        $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
        $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
        $records[$key]['comments'] = $PMDR->get('Cleaner')->clean_output($record['comments']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('checkmark',array('label'=>$PMDR->getLanguage('admin_listings_claims_approve'),'href'=>URL_NOQUERY.'?action=approve&id='.$record['id'].'&user_id='.$record['user_id'].'&listing_id='.$record['listing_id'],'onclick'=>'return confirm(\''.$PMDR->getLanguage('messages_confirm').'\');'));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&id='.$record['id'].'&user_id='.$record['user_id'].'&listing_id='.$record['listing_id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_listings_claims'));
    $template_content->set('content',$table_list->render());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>