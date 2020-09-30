<?php
/**
* Verifies social URLs and extracts/appends IDs
*/
class Social_Links {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Erroc code
    * @var int
    */
    var $error;

    /**
    * Social links constructor
    * @param object $PMDR Registry
    * @param int $error Error code
    * @return void
    */
    function __construct($PMDR,$error) {
        $this->PMDR = $PMDR;
        $this->types = array(
            'facebook'=>'http://facebook.com/*',
            'instagram'=>'http://instagram.com/*',
            'twitter'=>'http://twitter.com/*',
            'google'=>'http://plus.google.com/*',
            'linkedin_url'=>'http://linkedin.com/pub/*',
            'linkedin_company'=>'http://linkedin.com/company/*',
            'youtube'=>'http://youtube.com/user/*',
            'pinterest'=>'http://pinterest.com/*',
            'foursquare'=>'http://foursquare.com/*',
        )
    }

    function getURL($type, $id) {
        if(!in_array($type,$this->types)) {
            return false;
        }
        return str_replace('*',$id,$this->types[$type]);
    }
}
?>