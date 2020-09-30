<?php
if(!IN_PMD) exit();

$form->addField('mailchimp_api_key','text',array('label'=>'MailChimp API Key','fieldset'=>'details'));
$form->addField('mailchimp_webhook_secret','text',array('label'=>'Secret Key','fieldset'=>'details'));
$form->addFieldNote('mailchimp_webhook_secret','Enter a random value for the secret key.  This is used to validate web hooks from MailChimp.');
if(!empty($details['mailchimp_webhook_secret'])) {
    $form->addField('mailchimp_webhook_url','custom',array('label'=>'Webhook URL','value'=>BASE_URL.'/modules/email_marketing/MailChimp/webhooks.php?key='.$details['mailchimp_webhook_secret'],'fieldset'=>'details'));
}
$form->addValidator('mailchimp_api_key',new Validate_NonEmpty());
$form->addValidator('mailchimp_webhook_secret',new Validate_NonEmpty());
?>