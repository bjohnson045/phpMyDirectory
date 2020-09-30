<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_favorites'));

/** @var AuthenticationUser */
$PMDR->get('Authentication')->authenticate();

$users = $PMDR->get('Users');
$favorites = $PMDR->get('Favorites');

$user = $users->getRow($PMDR->get('Session')->get('user_id'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_favorites'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_favorites.php','text'=>$PMDR->getLanguage('user_favorites')));

if($_GET['action'] == 'delete') {
    if($favorites->delete(array('id'=>$_GET['id'],'user_id'=>$user['id']))) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_favorites'))),'delete');
    }
    redirect();
}

$table_list = $PMDR->get('TableList');
$paging = $PMDR->get('Paging');
$table_list->addColumn('title',$PMDR->getLanguage('user_favorites_title'));
$table_list->addColumn('link',$PMDR->getLanguage('user_favorites_link'));
$table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS f.id, f.listing_id, l.title, l.friendly_url FROM ".T_FAVORITES." f INNER JOIN ".T_LISTINGS." l ON f.listing_id=l.id WHERE f.user_id=? LIMIT ".$paging->limit1.",".$paging->limit2,array($user['id']));
$paging->setTotalResults($db->FoundRows());
foreach($records as $key=>$record) {
    $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
    $records[$key]['url'] = $PMDR->get('Listings')->getURL($record['listing_id'],$record['friendly_url']);
}
$table_list->addRecords($records);
$table_list->addPaging($paging);

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_favorites.tpl');
$table_list->addToTemplate($template_content);

include(PMDROOT.'/includes/template_setup.php');
?>