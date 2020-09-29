<?php
class Authentication_MemberKit extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("
        SELECT
            lastName AS user_last_name,
            firstName AS user_first_name,
            country AS user_country,
            state AS user_state,
            postalCode AS user_zip,
            city AS user_city,
            address AS user_address1,
            phone AS user_phone,
            login,
            password AS pass,
            email AS user_email
        FROM ".$this->PMDR->getConfig('login_module_db_prefix')."profile
        WHERE login=? AND password=?",array($this->username,$this->password));
    }
}
?>