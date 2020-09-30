<?php
/**
* Email Lists
*/
class Email_Lists extends TableGateway {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;

    /**
    * Email Lists constructor
    * @param object $PMDR
    * @return Email_Lists
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_EMAIL_LISTS;
    }

    /**
    * Insert an email list into the database
    * @param array $data Email list data
    * @return int Email list ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->checkAddAllUsers($data, $id);
        return $id;
    }

    /**
    * Update an existing email list
    * @param array $data Email List data
    * @param int $id Email list ID
    * @return resource
    */
    function update($data, $id) {
        $list = $this->db->GetRow("SELECT * FROM ".T_EMAIL_LISTS." WHERE id=?",array($id));

        if($this->PMDR->get('Email_Marketing') AND empty($data['email_marketing_list_id'])) {
            $this->PMDR->get('Email_Marketing')->unlinkList($id);
        } elseif($data['email_marketing_list_id'] != $list['email_marketing_list_id']) {
            $this->PMDR->get('Email_Marketing')->syncList($id);
        }
        $result = parent::update($data, $id);
        $this->checkAddAllUsers($data, $id);
        return $result;
    }

    /**
    * Check if add all users option selected and add all users to a list
    * @param array $data Email list data
    * @param int $id List ID
    */
    protected function checkAddAllUsers($data, $id) {
        // Check if they also wanted to add all the users to the list
        if (!empty($data['addall'])) {
            // Add the subscriptions to the email marketing queue
            if($email_marketing = $this->PMDR->get('Email_Marketing')) {
                $email_marketing->queueAll($id);
            }
            // Then, add everyone to the list.
            $this->db->Execute("INSERT INTO ".T_EMAIL_LISTS_LOOKUP." (list_id,user_id) SELECT ?, user.id FROM ".T_USERS." AS user WHERE user.id NOT IN (SELECT user_id FROM ".T_EMAIL_LISTS_LOOKUP." WHERE list_id=?)",array($id,$id));
        }
    }

    /**
    * Delete an email list
    * @param int $id List ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_EMAIL_LISTS_LOOKUP." WHERE list_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_EMAIL_MARKETING_QUEUE." WHERE list_id=?",array($id));
        parent::delete($id);
    }
}
?>