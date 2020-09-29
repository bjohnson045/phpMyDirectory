<?php
class Authentication_GeoClassifieds extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("
        SELECT
            l.username AS login,
            l.password AS pass,
            u.email AS user_email,
            u.firstname AS user_first_name,
            u.lastname AS user_last_name,
            u.company_name AS user_organization,
            u.address AS user_address1,
            u.address_2 AS user_address2,
            u.zip AS user_zip,
            u.city AS user_city,
            u.state AS user_state,
            u.country AS user_country,
            u.phone AS user_phone,
            u.fax AS user_fax
        FROM ".$this->PMDR->getConfig('login_module_db_prefix')."userdata u
        INNER JOIN ".$this->PMDR->getConfig('login_module_db_prefix')."logins l ON u.id = l.id
        WHERE l.username=? AND l.password=?",array($this->username,$this->password));
    }
}
?>