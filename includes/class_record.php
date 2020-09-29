<?php
/**
* Record
*/
class Record implements ArrayAccess {
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
    * Record data
    * @var array
    */
    var $data = array();

    /**
    * Record constructor
    * @param object $PMDR
    * @param int $id Load data based on ID
    * @return Record
    */
    function __construct($PMDR, $id) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        if(!$this->load($id)) {
            return false;
        }
        return $this;
    }

    /**
    * Magic get function to access data
    * @param string $name Data key name to get
    * @return mixed Null if nothing found, otherwise the data
    */
    public function __get($name) {
        if(is_array($this->data) AND array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    /**
    * Magic isset function for checking if data is set
    * @param string $name Data key name to check
    * @return bool
    */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
    * Magic unset for unsetting a data item
    * @param string $name Data key name to unset
    * @return void
    */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
    * Set data
    * @param string $offset Data key name to check
    * @param mixed $value The value to set
    * @return void
    */
    public function offsetSet($offset, $value) {
        if(is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
    * Function for checking if data is set
    * @param string $offset Data key name to check
    * @return bool
    */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
    * Unset a data item
    * @param string $offset Data key name to unset
    * @return void
    */
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    /**
    * Function to access data
    * @param string $offset Data key name to get
    * @return mixed Null if nothing found, otherwise the data
    */
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}
?>
