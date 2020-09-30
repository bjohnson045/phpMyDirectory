<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->set('wrapper_file',null);
$PMDR->set('header_file',null);
$PMDR->set('footer_file',null);

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('user_invoices','user_transactions'));

if($invoice = $PMDR->get('Invoices')->getRow(array('id'=>$_GET['id'],'user_id'=>$PMDR->get('Session')->get('user_id')))) {
    $template_content = $PMDR->get('Invoices')->getPrintTemplate($invoice['id'],PMDROOT.TEMPLATE_PATH.'members/user_invoices_print.tpl');
} else {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices.php');
}

include(PMDROOT.'/includes/template_setup.php');
?>