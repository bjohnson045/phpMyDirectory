<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_listings'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_listings_suggestions_view');

/** @var Listings */
$listings = $PMDR->get('Listings');
/** @var Listings_Suggestions */
$listings_suggestions = $PMDR->get('Listings_Suggestions');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_listings_suggestions_edit');
    $listings_suggestions->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_listing_suggestions'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_listings_suggestions_listing_id'));
    $table_list->addColumn('user_id',$PMDR->getLanguage('admin_listings_suggestions_user_id'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_listings_suggestions_date'));
    $table_list->addColumn('comments',$PMDR->getLanguage('admin_listings_suggestions_comment'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($listings_suggestions->getCount());
    $records = $db->GetAll("
        SELECT s.*, l.title, l.friendly_url, u.user_first_name, u.user_last_name, u.login
        FROM
            ".T_LISTINGS_SUGGESTIONS." s
                INNER JOIN ".T_LISTINGS." l ON s.listing_id = l.id
                INNER JOIN ".T_USERS." u ON u.id=s.user_id
        ORDER BY date ASC
        LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']
    );
    foreach($records as $key=>$record) {
        $records[$key]['listing_id'] = '<a href="'.$listings->getURL($record['listing_id'],$record['friendly_url']).'">'.$record['title'].'</a>';
        $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
        $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
        $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['comments'] = $PMDR->get('Cleaner')->clean_output($record['comments']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_listings_suggestions'));
    $template_content->set('content',$table_list->render());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>