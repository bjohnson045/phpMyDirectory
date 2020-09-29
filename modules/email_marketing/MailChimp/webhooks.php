<?php
include('../../../defaults.php');

if($email_marketer = $PMDR->get('Email_Marketer')) {
    if(isset($_POST) AND isset($_GET['key']) AND $email_marketer->settings['webhook_key'] == $_GET['key']) {
        switch($_POST['type']) {
            case 'subscribe':
                $email_marketer->subscribe($_POST['data']['list_id'],$_POST['data']['email']);
                break;
            case 'unsubscribe':
                $email_marketer->unsubscribe($_POST['data']['list_id'],$_POST['data']['email']);
                break;
        }
    }
}
?>