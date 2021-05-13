<?php
include('../../defaults.php');

$template_content = $PMDR->getNew('Template',PMDROOT.'/install/template/import_index.tpl');

$form = $PMDR->get('Form');
$form->addFieldSet('upgrade',array('legend'=>''));
$form->addField('import_from','select',array('label'=>'Import from','fieldset'=>'upgrade','value'=>'','options'=>array('11-04-00'=>'phpMyDirectory v11.04.00','10-4-6'=>'phpMyDirectory v10.4.6')));
$form->addField('login','text',array('label'=>'Administrator Username','fieldset'=>'upgrade'));
$form->addField('pass','text',array('label'=>'Administrator Password','fieldset'=>'upgrade'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    if(!$user = $db->GetRow("SELECT * FROM ".T_USERS." WHERE (login=? OR user_email=?)",array($data['login'],$data['login']))) {
        $form->addError('Login failed.');
    } elseif(isset($user['password_hash'])) {
        if($user['pass'] != hash($user['password_hash'],$data['pass'].$user['password_salt'])) {
            $form->addError('Login failed.');
        }
    } else {
        if($user['pass'] != md5($data['pass'])) {
            $form->addError('Login failed.');
        }
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $_SESSION['login'] = $data['login'];
        $_SESSION['pass'] = md5($data['pass']);
        $_SESSION['import_hash'] = md5($data['login'].md5($data['pass']));

        switch($data['import_from']) {
            case '11-04-00':
                redirect(BASE_URL.'/install/import/11-04-00/index.php');
                break;
            case '10-4-6':
                redirect(BASE_URL.'/install/import/10-4-6/index.php');
                break;
            default:
                redirect(BASE_URL.'/install/index.php');
                break;
        }
    }
}
$template_content->set('content',$form->toHTML());

include(PMDROOT.'/install/includes/template_setup.php');
?>