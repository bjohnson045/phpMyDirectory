<?php
/**
* Class TableGateway
* Generic class used for genreal table access objects
*/
class TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * @var string $table Table name for data
    */
    var $table;

    /**
    * TableGateway Constructor
    * @param object $PMDR Registry
    * @param string $table Data table
    */
    function __construct($PMDR, $table) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->table = $table;
    }

    /**
    * Strip submit from data array
    * Used to remove submit button from any data (could remove this from here and put into form class)
    * @param array $data Data array
    * @return array Data
    */
    function stripFields($data, $fields) {
        foreach($fields as $field) {
            unset($data[$field]);
        }
        return $data;
    }

    /**
    * Get Private Key
    * @return array Primary keys array
    */
    function getPKey() {
        return $this->db->MetaPrimaryKeys($this->table);
    }

    /**
    * Get table columns
    * @return array Table columns
    */
    function getColumns() {
        return $this->db->MetaColumnNames($this->table,true);
    }

    /**
    * Get row from table
    * @param mixed $condition Conditions for select
    * @return array Row data
    */
    function getRow($condition) {
        if(!is_array($condition)) $condition = array('id'=>$condition);
        $query = "SELECT * FROM ".$this->table." WHERE ";
        $query_parts = array();
        foreach($condition as $column=>$value) {
            $query_parts[] = $column."=?";
        }
        return $this->db->GetRow($query.implode(' AND ',$query_parts),array_values($condition));
    }

    /**
    * Get Multiple rows
    * @param mixed $condition Conditions for select
    * @param array $order Order for select
    * @param integer $offset Offset of limit
    * @param integer $count Number of records to select
    * @return array Multi-dimensional array of results
    */
    function getRows($condition = array(), $order = NULL, $offset = NULL, $count = NULL, $return_type ='all') {
        $query = "SELECT * FROM ".$this->table;
        if(count($condition) > 0) {
            $query .= " WHERE ";
            foreach($condition as $column=>$value) {
                $query .= $column."=? AND ";
            }
            $query = rtrim($query,'AND ');
        }
        if(!is_null($order)) {
            $query .= " ORDER BY ";
            foreach($order as $column=>$direction) {
                $query .= $column." ".$direction.",";
            }
            $query = rtrim($query,',');
        }
        if(!is_null($offset) AND !is_null($count)) {
            $query .= " LIMIT ".$offset.",".$count;
        }
        $method = 'Get'.$return_type;
        return $this->db->$method($query,array_values($condition));
    }

    /**
    * Get rows by a offset and limit
    * @param int $offset Offset
    * @param int $count Count
    */
    function getRowsLimit($offset = NULL, $count = NULL) {
        return $this->getRows(array(), NULL, $offset, $count);
    }

    /**
    * Get rows in associative format
    * @param array $condition
    * @param string $order
    * @param int $offset
    * @param int $count
    */
    function getRowsAssoc($condition = array(), $order = NULL, $offset = NULL, $count = NULL) {
        return $this->getRows($condition = array(), $order = NULL, $offset = NULL, $count = NULL, 'assoc');
    }

    /**
    * Get count
    * @param mixed $conditoon Conditions for select
    * @return integer Count
    */
    function getCount($condition=array()) {
        $query = "SELECT COUNT(*) AS count FROM ".$this->table;
        if(count($condition) > 0) {
            $query .= " WHERE ";
            foreach($condition as $column=>$value) {
                $query .= $column."=? AND ";
            }
            $query = rtrim($query,'AND ');
        }
        return $this->db->GetOne($query,array_values($condition));
    }

    /**
    * Insert row
    * @param array $data Data array where all values match columns of the table
    * @return integer Insert ID
    */
    function insert($data) {
        $data = array_intersect_key($data,array_flip($this->getTableFields()));
        $this->db->Execute("INSERT INTO ".$this->table." (`".implode('`,`',array_keys($data))."`) VALUES (".rtrim(str_repeat('?,',count($data)),',').")",array_values($data));
        if(isset($data['id'])) {
            return $data['id'];
        } else {
            return $this->db->Insert_ID();
        }
    }

    /**
    * Get all of the fields from a table
    * @return array Fields
    */
    function getTableFields() {
        return $this->db->MetaColumnNames($this->table);
    }

    /**
    * Update row
    * @param array $data Data array
    * @param mixed $condition Conditions for update
    * @return boolean True if insert is successful
    */
    function update($data, $condition) {
        $data = array_intersect_key($data,array_flip($this->getTableFields()));
        unset($data['id']);
        if(!is_array($condition)) $condition = array('id'=>$condition);
        $query = "UPDATE ".$this->table." SET ";
        foreach($data as $column=>$value) {
            $query .= '`'.$column."`=?,";
        }
        $query = rtrim($query,',')." WHERE ";

        foreach($condition as $column=>$value) {
            $query .= $column."=?,";
        }
        return $this->db->Execute(rtrim($query,','),array_values(array_merge($data,$condition)));
    }

    /**
    * Perform a replace query
    * @param array $data Replace data
    * @return resource
    */
    function replace($data) {
        $data = array_intersect_key($data,array_flip($this->getTableFields()));
        $query = "INSERT INTO ".$this->table." (".implode(',',array_keys($data)).") VALUES (".rtrim(str_repeat('?,',count($data)),',').") ";
        $query .= "ON DUPLICATE KEY UPDATE ";
        foreach($data as $column=>$value) {
            $query .= $column."=".$this->PMDR->get('Cleaner')->clean_db($value).",";
        }
        return $this->db->Execute(rtrim($query,','),array_values($data));
    }

    /**
    * Delete row
    * @param mixed $condition Conditions for delete
    * @return boolean True if delete is successful
    */
    function delete($condition) {
        if(!is_array($condition)) $condition = array('id'=>$condition);
        $query = "DELETE FROM ".$this->table." WHERE ";
        $query .= implode('=? AND ',array_keys($condition)).'=?';
        return $this->db->Execute(rtrim($query,','),array_values($condition));
    }
}
?>