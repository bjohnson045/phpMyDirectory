<?php
/**
* Class vCard
* Constructs given data into a valid vCard format
*/
class vCard {
    /**
    * @var array $data vCard data
    */
    var $data;
    /**
    * @var Database
    */
    var $db;
    /**
    * @var Registry
    */
    var $PMDR;

    /**
    * vCard Constructor
    * @param object $PMDR Registry
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Add telephone data
    * @param string $phone Phone Number
    * @return void
    */
    function addTelephone($phone) {
        $this->data .= "TEL;PREF;WORK:$phone\r\n";
    }

    /**
    * Add Mobile Telephone Number
    * @param string $cell Mobile Number
    * @return void
    */
    function addMobile($cell) {
        $this->data .= "TEL;CELL:$cell\r\n";
    }

    /**
    * Add Fax Number
    * @param string $fax Fax Number
    * @return void
    */
    function addFax($fax) {
        $this->data .= "TEL;FAX:$fax\r\n";
    }

    /**
    * Add Email Address
    * @param string $email Email Address
    * @return void
    */
    function addEmail($email) {
        $this->data .= "EMAIL;TYPE=INTERNET;TYPE=PREF:$email\r\n";
    }

    /**
    * Add Website URL
    * @param string $url Website URL
    * @return void
    */
    function addURL($url) {
        $this->data .= "URL;WORK:$url\r\n";
    }

    /**
    * Add Note
    * @param string $note Note
    * @return void
    */
    function addNote($note) {
        $this->data .= "NOTE;ENCODING=QUOTED-PRINTABLE:$note\r\n";
    }

    /**
    * Add First Name
    * @param string $name First Name
    * @return void
    */
    function addFirstName($name) {
        $this->data .= "FN:$name\r\n";
    }

    /**
    * Add Name
    * @param string $name Full Name
    * @return void
    */
    function addName($name) {
        $this->data .= "N:;$name\r\n";
    }

    /**
    * Add Organization
    * @param string $org Organization Name
    * @return void
    */
    function addOrganization($org) {
        $this->data .= "ORG:$org\r\n";
    }

    /**
    * Add Address
    * @param string $address Street Address
    * @param string $city City
    * @param string $state State
    * @param string $country Country
    * @param string $zip Zip Code
    * @return void
    */
    function addAddress($address, $city, $state, $country, $zip) {
        $this->data .= "ADR;TYPE=WORK:;;$address;$city;$state;$zip;$country\r\n";
    }

    /**
    * Add vCard header
    * @return void
    */
    function addHeader() {
        $this->data = "BEGIN:VCARD\nVERSION:3.0\r\nPROFILE:VCARD\r\n".$this->data;
    }

    /**
    * Add vCard footer
    * @return void
    */
    function addFooter() {
        $this->data .= "END:VCARD";
    }

    /**
    * Get vCard formatted
    * @return string vCard data formatted
    */
    function getCard() {
        $this->addHeader();
        $this->addFooter();
        return $this->data;
    }
}
?>