<?php
/**
* Zip Codes
*/
class Zip_Codes extends TableGateway {
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
    * Zip codes constructor
    * @param object $PMDR
    * @return Zip_Codes
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_ZIP_DATA;
    }

    /**
    * Clear all zip codes
    * @return void
    */
    function clear() {
        $this->db->Execute("TRUNCATE ".T_ZIP_DATA);
    }

    /**
    * Search zip codes
    * @param mixed $zipcode Zipcode
    * @param int $radius Radius in miles
    * @param int $limit1 Limit start
    * @param int $limit2 Limit number
    * @return array Zip codes results
    */
    function search($zipcode, $radius = null, $limit1 = null, $limit2 = null) {
        $zipcode = $this->db->GetAll("SELECT * FROM ".T_ZIP_DATA." WHERE zipcode=?",array($zipcode));
        if(!$zipcode) {
            return array();
        } elseif(is_null($radius)) {
            return $zipcode;
        } else {
            $query = "SELECT ".(!is_null($limit1) ? 'SQL_CALC_FOUND_ROWS ' : '')."* FROM ".T_ZIP_DATA." WHERE SQRT(POW(69.1*(lat-".$zipcode[0]['lat']."),2)+POW(53*(lon-".$zipcode[0]['lon']."),2)) <= ?";
            if(!is_null($limit1)) {
                $query .= " LIMIT $limit1";
            }
            if(!is_null($limit2)) {
                $query .= ",$limit2";
            }
            return $this->db->GetAll($query,array($radius));
        }
    }
}
?>