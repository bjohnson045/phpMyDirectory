<?php
// Define the current section being accessed
define('PMD_SECTION','members');

// Include initialization script
include('../defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('user_cancellations','email_templates'));

// Authenticate teh user is logged in
$PMDR->get('Authentication')->authenticate();

// Set the page title
$PMDR->setAdd('page_title',$PMDR->getLanguage('user_cancellations_cancellation'));

// Get the user from the session ID
$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

// Get the order from the database
$order = $db->GetRow("SELECT * FROM ".T_ORDERS." WHERE id=?",array($_GET['id']));

// If the order does not belong to the user, redirect to the user index page
if($user['id'] != $order['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

// See if an existing cancellation request exists, if so, redirect back to the orders with a message
if($db->GetOne("SELECT COUNT(*) FROM ".T_CANCELLATIONS." WHERE order_id=?",array($order['id']))) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_cancellations_pending'));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

// Load the template file
// Fill out the form in the template file
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_cancellations.tpl');

// Set up the cancellation request form
$form = $PMDR->get('Form');
$form->addFieldSet('cancellation',array('legend'=>$PMDR->getLanguage('user_cancellations_cancellation')));
$form->addField('order_id','custom',array('label'=>$PMDR->getLanguage('user_cancellations_order_number'),'fieldset'=>'cancellation','value'=>$order['order_id']));
$form->addField('comment','textarea',array('label'=>$PMDR->getLanguage('user_cancellations_comment'),'fieldset'=>'cancellation'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

// Set up form validators
$form->addValidator('comment',new Validate_NonEmpty());

// Set the form in the template
$template_content->set('form',$form);

// Process the submission of the cancellation form
if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        // If the submission was successful display a message, add the cancellation to the database, and email a notification to the admin
        $PMDR->addMessage('success',$PMDR->getLanguage('user_cancellations_submitted'));
        $db->Execute("INSERT INTO ".T_CANCELLATIONS." (user_id,order_id,date,comment) VALUES (?,?,NOW(),?)",array($PMDR->get('Session')->get('user_id'),$order['id'],$data['comment']));
        $PMDR->get('Email_Templates')->send('admin_cancellation_request',array('variables'=>$data,'order_id'=>$order['id']));
        redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>