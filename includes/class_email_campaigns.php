<?php
/**
* Email Campaigns
*/
class Email_Campaigns extends TableGateway {
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
    * Email Campaigns constructor
    * @param object $PMDR
    * @return Email_Campaigns
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_EMAIL_CAMPAIGNS;
    }

    /**
    * Insert an email campaign
    * @param array $data Email campaign data
    * @return void
    */
    function insert($data) {
        if(!empty($data['attachment'])) {
            move_uploaded_file($data['attachment']['tmp_name'], TEMP_UPLOAD_PATH.basename($data['attachment']['name']));
            $data['attachment_mimetype'] = $data['attachment']['type'];
            $data['attachment'] = basename($data['attachment']['name']);
        } else {
            unset($data['attachment']);
        }
        parent::insert($data);
    }

    /**
    * Update an email campaign
    * @param array $data Email campaign data
    * @param int $id Email campaign ID
    * @return void
    */
    function update($data, $id) {
        if(!empty($data['attachment'])) {
            move_uploaded_file($data['attachment']['tmp_name'], TEMP_UPLOAD_PATH.basename($data['attachment']['name']));
            $data['attachment_mimetype'] = $data['attachment']['type'];
            $data['attachment'] = basename($data['attachment']['name']);
        } else {
            unset($data['attachment']);
        }
        parent::update($data,$id);
    }

    /**
    * Delete an email campaign
    * @param int $id Email campaign ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE campaign_id=?",array($id));
        parent::delete($id);
    }
}
?>