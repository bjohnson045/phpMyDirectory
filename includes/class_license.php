<?php
if($hooks['AEfd@34D2167G5ffss#4fS'] != '32dG563#dfg^uhf$sCDED3Ds2') exit('License error (1)');
$hooks['sd%343Dvdsf#23CsddsD#df'] = 'sdfsE#32DG65$%$@dszcdfE#';

/**
* Licensing Class.
*/
final class PMDLicense {
    var $PMDR;
    var $db;
    var $url = "http://www.phpmydirectory.com/order/";
    var $hostname = "www.phpmydirectory.com";
    var $checkdate;
    var $ip;
    var $domain;
    var $localkeydays = 30; # How long the local key is valid for in between remote checks
    var $allowcheckfaildays = 7; # How many days to allow after local key expiry before blocking access if connection cannot be made
    var $license;
    var $local_key = 'key.php';
    var $debug = false;
    var $debug_message = '';

    /**
    * License constructor
    * @param object $PMDR
    * @return PMDLicense
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->license = LICENSE;
        $this->checkdate = date("Ymd");
        if(!isset($_SERVER)) {
            $this->domain = parse_url(BASE_URL,PHP_URL_HOST);
            $this->ip = gethostbyname($this->domain);
        } else {
            $this->domain = $_SERVER['SERVER_NAME'];
            $this->ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
        }
        if(isset($_GET['debug'])) {
            $this->debug = true;
        }
        $this->directory = dirname(__FILE__);
    }

    private function getCheckToken() {
        static $check_token = null;
        if($check_token === null) {
            $check_token = time().md5(mt_rand(1000000000,9999999999).LICENSE);
        }
        return $check_token;
    }

    /**
    * Validate the license and redirect if needed
    */
    function validate($parameters = null) {
        $results = $this->getResults();
        if($this->debug) {
            echo $this->debug_message;
            exit();
        } elseif($results['status'] != 'Active') {
            redirect(BASE_URL_ADMIN.'/admin_license_error.php?type=invalid');
            exit();
        } elseif(!$results['copyright_compliance']) {
            redirect(BASE_URL_ADMIN.'/admin_license_error.php?type=branding');
            exit();
        } elseif(!$results['integrity']) {
            redirect(BASE_URL_ADMIN.'/admin_license_error.php?type=integrity');
            exit();
        } elseif(!is_null($parameters) AND isset($parameters['addons'])) {
            if(!is_array($parameters['addons'])) {
                $parameters['addons'] = array($parameters['addons']);
            }
            foreach($parameters['addons'] AS $addon) {
                if(!$results['addons_flags'][$addon]) {
                    redirect(BASE_URL_ADMIN.'/admin_license_error.php?type=addon');
                    exit();
                }
            }
        }
    }

    /**
    * Validate but do not redirect
    * @return boolean
    */
    function validateSilent($local_only = false) {
        if($local_only) {
            $local_results = $this->getResultsLocal();
            if(in_array($local_results['status'],array('Invalid','Expired'))) {
                return false;
            }
        }
        $results = $this->getResults();
        if($results['status'] != 'Active' OR !$results['integrity']) {
            return false;
        }
        return true;
    }

    /**
    * Get license results/status
    */
    private function getResults() {
        $this->debug_message .= 'Validating license<br>';
        // Check the local key first
        $local_results = $this->getResultsLocal();
        if(in_array($local_results['status'],array('Invalid','Expired'))) {
            $this->debug_message .= 'Local key validation failed, checking remotely<br>';
            $results = $this->getRemoteResults();
            $this->debug_message .= 'Got remote results:<br>';
            $this->debug_message .= 'Status: '.$results['status'].'<br>';
            $this->debug_message .= 'Message: '.value($results,'message').'<br>';
            $this->debug_message .= 'Domain: '.$results['validdomain'].'<br>';
            $this->debug_message .= 'Addons '.$results['addons'].'<br>';
            $this->debug_message .= '<br>';
            if($results['status'] == 'Invalid') {
                $this->debug_message .= 'Result is invalid<br>';
                if(isset($local_results['checkdate']) AND $local_results['checkdate'] > date("Ymd",mktime(0,0,0,date("m"),date("d")-($this->localkeydays+$this->allowcheckfaildays),date("Y")))) {
                    $this->debug_message .= 'Falling back on local key<br>';
                    $results = $local_results;
                } elseif(file_exists($this->local_key)) {
                    $this->debug_message .= 'Found key.php file, checking validity<br>';
                    $key_string = file_get_contents($this->local_key);
                    $local_results = $this->parseLocalKey($key_string);
                    if($local_results['status'] == 'Active') {
                        $this->debug_message .= 'key.php valid, using<br>';
                        $results = $local_results;
                        $this->storeLocalKey($key_string);
                        unset($key_string);
                    } else {
                        $this->debug_message .= 'key.php invalid<br>';
                        $results['status'] = 'Invalid';
                    }
                } else {
                    $this->debug_message .= 'No valid local key, returning \'Invalid\'<br>';
                    $results['status'] = 'Invalid';
                }
            } else {
                $this->debug_message .= 'Turning remote key into local key and store<br>';
                $results["checkdate"] = $this->checkdate;
                $data_encoded = serialize($results);
                $data_encoded = base64_encode($data_encoded);
                $data_encoded = md5($this->checkdate.'5cd113c31e746315a368a8afe98bbe2e').$data_encoded;
                $data_encoded = strrev($data_encoded);
                $data_encoded = $data_encoded.md5($data_encoded.'5cd113c31e746315a368a8afe98bbe2e');
                $data_encoded = wordwrap($data_encoded,80,"\n",true);
                $results['localkey'] = $data_encoded;
                $this->debug_message .= 'Storing local key<br>';
                $this->storeLocalKey($results['localkey']);
            }
        } else {
            $this->debug_message .= 'Using local key since its valid<br>';
            $results = $local_results;
        }
        unset($local_results);

        $results['addons_flags'] = array(
            'ADDON_BLOG'=>false,
            'ADDON_LINK_CHECKER'=>false,
            'ADDON_DISCOUNT_CODES'=>false,
            'ADDON_UNBRANDING'=>false,
        );

        if(isset($results['addons'])) {
            $addon_strings = explode('|',$results['addons']);
            $results['addons'] = array();
            foreach($addon_strings AS $key=>$addon_string) {
                $addon_parts = explode(';',$addon_string);
                foreach($addon_parts AS $addon_part) {
                    $addon_value = explode('=',$addon_part);
                    if($addon_value[0] == 'name') {
                         $results['addons'][] = $addon_value[1];
                         switch($addon_value[1]) {
                            case 'Blog':
                                $results['addons_flags']['ADDON_BLOG'] = true;
                                break;
                            case 'Discount Codes':
                                $results['addons_flags']['ADDON_DISCOUNT_CODES'] = true;
                                break;
                            case 'Link Checker':
                                $results['addons_flags']['ADDON_LINK_CHECKER'] = true;
                                break;
                            case 'Unbranded Administrative Area':
                                $results['addons_flags']['ADDON_UNBRANDING'] = true;
                                break;
                        }
                    }
                }
            }
        } else {
            $results['addons'] = array();
        }
        unset($addons,$addon_strings,$key,$addon_string,$addon_parts,$addon_part,$addon_value);

        $this->db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='addons'",array(serialize($results['addons_flags'])));

        // Check copyright compliance
        $results['copyright_compliance'] = true;
        if($results['addons_flags']['ADDON_UNBRANDING'] == false) {
            $results['copyright_compliance'] = $this->validateCopyright();
        }

        // Check integrity
        $results['integrity'] = $this->validateIntegrity();

        return $results;
    }

    public function getResultsLocal() {
        return $this->parseLocalKey($this->getStoredLocalKey());
    }

    /**
    * Parse local key license
    * @param string $localkey
    * @return array Results
    */
    private function parseLocalKey($localkey) {
        $this->debug_message .= 'Parsing local key:<br>'.$localkey.'<br><br>';
        $localkeyresults['status'] = 'Invalid';
        $localkey = str_replace("\n",'',$localkey); # Remove the line breaks
        $localdata = substr($localkey,0,strlen($localkey)-32); # Extract License Data
        $md5hash = substr($localkey,strlen($localkey)-32); # Extract MD5 Hash
        if ($md5hash==md5($localdata.'5cd113c31e746315a368a8afe98bbe2e')) {
            $this->debug_message .= 'Passed hash check of local key<br>';
            $localdata = strrev($localdata); # Reverse the string
            $md5hash = substr($localdata,0,32); # Extract MD5 Hash
            $localdata = substr($localdata,32); # Extract License Data
            $localdata = base64_decode($localdata);
            $localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults["checkdate"];
            $this->debug_message .= 'Got local key results:<br>';
            $this->debug_message .= 'Status: '.$localkeyresults['status'].'<br>';
            $this->debug_message .= 'Domain: '.$localkeyresults['validdomain'].'<br>';
            $this->debug_message .= 'Addons '.$localkeyresults['addons'].'<br>';
            $this->debug_message .= '<br>';
            if($md5hash==md5($originalcheckdate.'5cd113c31e746315a368a8afe98bbe2e')) {
                $this->debug_message .= 'Passed second hash check of local key<br>';
                $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-$this->localkeydays,date("Y")));
                if ($originalcheckdate>$localexpiry) {
                    $this->debug_message .= 'Passed expiry check of local key<br>';
                    $validdomains = explode(",",$localkeyresults["validdomain"]);
                    if (!in_array($this->domain, $validdomains)) {
                        $this->debug_message .= 'Domain check failed, setting to \'Invalid\'<br>';
                        $localkeyresults["status"] = "Invalid";
                    }
                    $validips = explode(",",$localkeyresults["validip"]);
                    if (!isset($usersip) OR !in_array($usersip, $validips)) {
                        //$localkeyresults["status"] = "Invalid";
                    }
                    if ($localkeyresults["validdirectory"]!=dirname(__FILE__)) {
                        //$localkeyresults["status"] = "Invalid";
                    }
                } else {
                    $this->debug_message .= 'Failed expiry check of local key, setting status to \'Expired\'.<br>';
                    $localkeyresults["status"] = "Expired";
                    $localkeyresults["local_expire_date"] = $localexpiry;
                }
            } else {
                $localkeyresults["status"] = "Invalid";
                $this->debug_message .= 'Failed second hash check, setting status to \'Invalid\'.<br>';
            }
        } else {
            $localkeyresults["status"] = "Invalid";
            $this->debug_message .= 'Failed first hash check, setting status to \'Invalid\'.<br>';
        }
        $this->debug_message .= 'Final local key results:<br>';
        $this->debug_message .= 'Status: '.$localkeyresults['status'].'<br>';
        $this->debug_message .= 'Domain: '.value($localkeyresults,'validdomain').'<br>';
        $this->debug_message .= 'Addons '.value($localkeyresults,'addons').'<br>';
        $this->debug_message .= '<br>';
        return $localkeyresults;
    }

    /**
    * Get results from license server
    * @return array Results
    */
    private function getRemoteResults() {
        $postfields["licensekey"] = $this->license;
        $postfields["domain"] = $this->domain;
        $postfields["ip"] = $this->ip;
        $postfields["dir"] = $this->directory;
        if($this->getCheckToken() != '') {
            $postfields["check_token"] = $this->getCheckToken();
        }
        // Send version to server so we can check the version versus a date on the license.
        $postfields["version"] = $this->PMDR->getConfig('pmd_version');
        $data = false;

        $this->debug_message .= 'Using data:<br>';
        $this->debug_message .= 'Domain: '.$postfields['domain'].'<br>';
        $this->debug_message .= 'IP: '.$postfields['ip'].'<br>';
        $this->debug_message .= 'Directory: '.$postfields['dir'].'<br>';
        $this->debug_message .= 'Version: '.$postfields["version"].'<br>';
        $this->debug_message .= '<br>';

        if(function_exists("curl_exec")) {
            $this->debug_message .= 'Using CURL<br>';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url."modules/servers/licensing/verify.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // Supress "Expect" header as it causes problems on some servers
            curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));
            $data = curl_exec($ch);
            if(!$data) {
                $this->debug_message .= 'CURL failed ('.curl_error($ch).')<br>';
            }
            curl_close($ch);
        }

        if(!$data) {
            $this->debug_message .= 'Using socket<br>';
            $fp = fsockopen($this->hostname, 80, $errno, $errstr, 5);
            if ($fp) {
                $querystring = http_build_query($postfields);
                $header="POST /order/modules/servers/licensing/verify.php HTTP/1.0\r\n";
                $header.="Host: ".$this->hostname."\r\n";
                $header.="Content-type: application/x-www-form-urlencoded\r\n";
                $header.="Content-length: ".@strlen($querystring)."\r\n";
                $header.="Expect:\r\n";
                $header.="Connection: close\r\n\r\n";
                $header.=$querystring;
                $data="";
                @stream_set_timeout($fp, 20);
                @fputs($fp, $header);
                $status = @socket_get_status($fp);
                while (!@feof($fp)&&$status) {
                    $data .= @fgets($fp, 1024);
                    $status = @socket_get_status($fp);
                }
                @fclose ($fp);
                if(!empty($errstr)) {
                    $this->debug_message .= 'Socket failed ('.$errstr.')<br>';
                }
            }
        }
        if(!$data) {
            $this->debug_message .= 'Remote license check failed<br>';
            return array('status'=>'Invalid');
        } else {
            $this->debug_message .= 'Raw data:<br>'.htmlspecialchars($data).'<br>';
            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $results = array();
            foreach ($matches[1] AS $k=>$v) {
                $results[$v] = $matches[2][$k];
            }
            if($results['md5hash']) {
                if($results['md5hash']!=md5('5cd113c31e746315a368a8afe98bbe2e'.$this->getCheckToken())) {
                    $this->debug_message .= 'MD5 Checksum Failed<br>';
                    return array('status'=>'Invalid','MD5 Checksum Failed');
                }
            }
            if(!isset($results['status'])) {
                return array('status'=>'Invalid','message'=>'Status key not in results: '.$data);
            } else {
                return $results;
            }
        }
    }

    /**
    * Write the local license key to somewhere.
    * @param string $local_key  The local key data to write.
    * @return You choose.
    */
    private function storeLocalKey($local_key) {
        return $this->db->Execute("UPDATE ".T_SETTINGS." SET value='{$local_key}' WHERE varname='license_local_key'");
    }

    /**
    * Get the local key from where you stored it.
    * @return string The local license key.
    */
    private function getStoredLocalKey() {
        $this->debug_message .= 'Getting stored local key from database<br>';
        return $this->db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='license_local_key'");
    }

    /**
    * Check the logo.png image files XMP data for matching and also parse the footer for the phpMyDirectory string
    * @return boolean
    */
    private function validateCopyright() {
        if(!$source = file_get_contents(PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'images/logo.png')) {
            trigger_error('Logo file not found for branding: '.PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'images/logo.png',E_USER_WARNING);
            $this->debug_message .= 'Logo file not found<br>';
            return false;
        }
        $xmpdata_start = strpos($source,"<x:xmpmeta");
        $xmpdata_end = strpos($source,"</x:xmpmeta>");
        $xmplength = $xmpdata_end-$xmpdata_start;
        $xmpdata = substr($source,$xmpdata_start,$xmplength+12);
        preg_match('/<dc:rights>\s*<rdf:Alt>\s*<rdf:li xml:lang="x-default">(.+)<\/rdf:li>\s*<\/rdf:Alt>\s*<\/dc:rights>/', $xmpdata, $matches);
        unset($source,$xmpdata);
        if($matches[1] != 'Accomplish Technology, LLC') {
            trigger_error('Corrupt logo for branding',E_USER_WARNING);
            $this->debug_message .= 'Logo has mismatched XPM data<br>';
            return false;
        }
        // Check a pixel color to see if it matches to prevent users simply editing the current .png file leaving XMP data in place
        $image = imagecreatefrompng(PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'images/logo.png');
        if(imagecolorat($image,10,20) != 10991529) {
            trigger_error('Logo manipulated for branding',E_USER_WARNING);
            $this->debug_message .= 'Logo manipulated<br>';
            return false;
        }
        $footer_eval_string = @file_get_contents(PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_footer.tpl');
        $footer_eval_string = preg_replace('/<!--.*-->/sU', '', $footer_eval_string);
        if(!strstr($footer_eval_string,'<?php echo $copyright; ?>')) {
            trigger_error('Footer copyright invalid',E_USER_WARNING);
            $this->debug_message .= 'Footer copyright invalid<br>';
            return false;
        }
        return true;
    }

    /**
    * Check certain files to make sure they have not been tampered with
    * @return boolean
    */
    private function validateIntegrity() {
        $integrity_check = true;
        $integrity_check = include(PMDROOT.'/includes/cron/cron_cleanup.php');
        return ($integrity_check == 'sdfskjfslkjf*454k5jkdjfskds');
    }

    /**
    * Clear the local key from the database
    */
    public function clearLocalKey() {
         $this->db->Execute("UPDATE ".T_SETTINGS." SET value='' WHERE varname='license_local_key'");
    }
}

// Used by factory to check if this has already been included
// Random string has no specific importance, just a string we can check against
return 'sdfsE#32DG65$%$@dszcdfE#';
?>