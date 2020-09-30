<?php
class Authentication_IPB extends Authentication_Module {
    function loadModuleUser() {
        $escape = array(
            '&'=>'&amp;',
            '\\'=>'&#092;',
            '!'=>'&#33;',
            '$'=>'&#036;',
            '"'=>'&quot;',
            '<'=>'&lt;',
            '>'=>'&gt;',
            '\''=>'&#39;'
        );
        $password_escaped = str_replace(array_keys($escape),array_values($escape),$this->password);

        $user = $this->db->GetRow("SELECT name AS login, members_pass_hash AS pass, members_pass_salt AS password_salt, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."core_members WHERE (name=? OR email=?)",array($this->username,$this->username));
        if(crypt($password_escaped,'$2a$13$'.$user['members_pass_salt']) != $user['members_pass_hash']) {
            return false;
        }
        return true;
    }
}
?>