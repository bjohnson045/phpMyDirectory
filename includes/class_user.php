<?php
/**
* User class
*/
class User extends Record {
    /**
    * Load a user
    * @param int $id User ID
    * @return bool
    */
    function load($id) {
        if(!$this->data = $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE id=?",array($id))) {
            return false;
        }
        return true;
    }

    /**
    * Get a users profile image URL
    * @return string Image URL
    */
    function getProfileImageURL() {
        return $this->PMDR->get('Users')->getProfileImage($this->data['id'],$this->user_email);
    }

    /**
    * Check if the user is logged in
    * @return bool
    */
    function loggedIn() {
        if(isset($this->logged_in)) {
            return $this->logged_in;
        } else {
            if($this->db->GetOne("SELECT COUNT(*) FROM ".T_SESSIONS." WHERE user_id=?",array($this->id))) {
                return $this->data['logged_in'] = true;
            }
            return false;
        }
    }

    /**
    * Get the admin summary header template
    * @param mixed $active Null if nothing is active, otherwise a string for the active element
    * @return Template
    */
    function getAdminSummaryHeader($active = null) {
        $template_content_header = $this->PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_summary_header.tpl');
        $template_content_header->set('active',$active);
        $template_content_header->set('id',$this->data['id']);
        if(trim($this->data['user_first_name'].' '.$this->data['user_last_name']) == '') {
            $template_content_header->set('name',$this->data['login']);
        } else {
            $template_content_header->set('name',$this->data['user_first_name'].' '.$this->data['user_last_name']);
        }
        return $template_content_header;
    }

    /**
    * Check if the user is missing required profile data
    * @return bool
    */
    function requiredDataMissing() {
        $required_value_missing = false;
        if($this->data['login'] == '' OR $this->data['user_email'] == '' OR strstr($this->data['login'],'RemoteLogin')) {
            $required_value_missing = true;
        }

        $required_fields = array(
            'user_first_name',
            'user_last_name',
            'user_organization',
            'timezone',
            'user_address1',
            'user_address2',
            'user_city',
            'user_state',
            'user_country',
            'user_zip',
            'user_phone',
            'user_fax'
        );

        if($this->PMDR->getConfig('user_display_name')) {
            $required_fields[] = 'display_name';
        }

        foreach($required_fields as $check) {
            if($this->PMDR->getConfig('user_add_'.$check) == 'required' AND empty($this->data[$check])) {
                $required_value_missing = true;
                break;
            }
        }

        if($required_value_missing OR ($this->PMDR->getConfig('reg_terms_checkbox') AND !$this->data['terms_accepted'])) {
            return true;
        } else {
            return false;
        }
    }
}
?>