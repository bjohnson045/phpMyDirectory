<?php
/**
* Documents class
*/
class Documents extends TableGateway{
    /**
    * Registry
    * @var object Registry
    */
    var $PMDR;

    /**
    * Database object
    * @var object $db
    */
    var $db;

    /**
    * Documents constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_DOCUMENTS;
    }

    /**
    * Insert a document into the database and copy necesarry files
    * @param array $data Document data to insert
    * @return int Document ID
    */
    function insert($data) {
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        $data['description'] = Strings::limit_characters($data['description'],$this->PMDR->getConfig('documents_desc_size'));
        $data['extension'] = get_file_extension($_FILES['document']['name']);
        unset($data['document']);
        $id = parent::insert($data);
        $this->processDocument($data,$id);
        return $id;
    }

    /**
    * Process document file, uploading it to documents folder
    * @param array $data Documents data
    * @param int $id Document ID
    */
    function processDocument($data,$id) {
        copy($_FILES['document']['tmp_name'],DOCUMENTS_PATH.$id.'.'.$data['extension']);
    }

    /**
    * Determine if document is a valid type
    * @param string $document Document filename
    * @return boolean True/False
    */
    function isValidType($document) {
        $extension = strtolower(get_file_extension($document));
        foreach(explode(',',$this->PMDR->getConfig('documents_allow')) as $file_type) {
            if($extension == strtolower(trim($file_type))) {
                return true;
            }
        }
        return false;
    }

    /**
    * Update document
    * @param array $data Document data
    * @param mixed $id Document ID
    * @return void
    */
    function update($data, $id) {
        $data['description'] = Strings::limit_characters($data['description'],$this->PMDR->getConfig('documents_desc_size'));
        if($_FILES['document']['name'] != '') {
            $data['extension'] = get_file_extension($_FILES['document']['name']);
            @unlink(find_file(DOCUMENTS_PATH.$id.'.*'));
            $this->processDocument($data,$id);
        }
        unset($data['document']); // get rid of this so we can use table gateway insert
        parent::update($data,$id);
    }

    /**
    * Delete document
    * @param int $id Document ID
    * @return void
    */
    function delete($id) {
        @unlink(find_file(DOCUMENTS_PATH.$id.'.*'));
        @unlink(find_file(DOCUMENTS_PATH.$id.'-small.*'));
        $this->db->Execute("DELETE FROM ".T_DOCUMENTS." WHERE id=?",array($id));
    }

    /**
    * Download document, serving it to the browser
    * @param int $id Document ID
    * @return void
    */
    function download($id) {
        $serve = $this->PMDR->get('ServeFile');
        $document = $this->getRow($id);
        $serve->serve(DOCUMENTS_PATH.$document['id'].'.'.$document['extension']);
    }
}
?>