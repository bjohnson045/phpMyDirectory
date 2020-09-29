<?php
class Login_Block extends Template_Block {
    function content() {
        if($this->PMDR->getConfig('block_login_show')) {
            $block_login_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_login.tpl');
            $form = $this->PMDR->getNew('Form');
            $form->setName('block_login');
            $form->action = BASE_URL_SSL.MEMBERS_FOLDER.'index.php?from='.urlencode_url(URL);
            $form->addField('user_login','text',array('label'=>$this->PMDR->getLanguage('block_login_username')));
            $form->addField('user_pass','password',array('label'=>$this->PMDR->getLanguage('block_login_password'),'autocomplete'=>'off'));
            $form->addField('remember','checkbox',array('label'=>'','html'=>$this->PMDR->getLanguage('block_login_remember')));
            $form->addField('submit_login','submit',array('label'=>$this->PMDR->getLanguage('block_login_submit'),'fieldset'=>'button'));
            $form->addValidator('login',new Validate_NonEmpty());
            $form->addValidator('pass',new Validate_NonEmpty());
            $block_login_template->set('form',$form);
            $block_login_template->set('title',$this->PMDR->getLanguage('block_login'));
            return $block_login_template;
        }
    }
}
?>