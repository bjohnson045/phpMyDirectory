<?php
/**
* Class LinkChecker
* Checks URLs or links for dead links and reciprocal links
*/
class LinkChecker {
    /**
    * Registry
    * @var object Register
    */
    var $PMDR;
    /**
    * Database
    * @var object
    */
    var $db;
    /**
    * URL to be checked for when checking for reciprocal links
    * @var string
    */
    var $check_url;

    /**
    * LinkChecker contructor
    * @param object $PMDR Registry
    * @return LinkChecker
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Check a URL and return the result
    * @param string $url
    * @param mixed $check_url
    * @return string
    */
    public function checkURL($url, $check_url = null) {
        if(trim($url) == '' OR trim($url) == 'http://') return false;

        if(is_null($check_url)) {
            $check_url = $this->check_url;
        }

        if(strstr($url,parse_url($check_url,PHP_URL_HOST))) {
            return false;
        }

        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings[CURLOPT_RETURNTRANSFER] = true;
        $http_request->settings[CURLOPT_HEADER] = true;
        $http_request->settings[CURLOPT_CONNECTTIMEOUT] = 5;
        $http_request->settings[CURLOPT_TIMEOUT] = 5;
        $http_request->settings[CURLOPT_MAXREDIRS] = 20;
        $http_request->settings[CURLOPT_FOLLOWLOCATION] = true;

        // This enables cookie handling.
        $http_request->settings[CURLOPT_COOKIEFILE] = '';

        // Some sites would error if a language wasn't provided.
        $http_request->settings[CURLOPT_HTTPHEADER] = array(
            'Accept-Language: en-US,en;q=0.8'
        );

        // Some sites are picky and want some user agent set.
        $http_request->settings[CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.151 Safari/535.19';

        $content = $http_request->get('curl',$url);

        // If curl errored, or didn't return a 200 reponse
        if($content == false || $http_request->response != 200) {
            return 'dead';
        }
        if(!$this->hasLink((!is_null($check_url) ? $check_url : $this->check_url), $content)) {
            return 'no_reciprocal';
        }
        return 'valid';
    }

    /**
    * Get number of failed links
    * @return int Number of failed links
    */
    public function getFailedCount() {
        return $this->db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE (www_status=0 OR (www_reciprocal=0 AND require_reciprocal=1))");
    }

    /**
    * Build the links query
    * @param int $process_number The number of links to process
    * @param int $day_buffer The number of days to wait to process a link if it was already processed
    * @param boolean $require_reciprocal Require a reciprocal link
    * @param boolean $failed_only Get links that have only failed previously
    * @param mixed $limit_start Specify the query limit starting point, null if no starting limit
    * @return string Query
    */
    private function buildLinksQuery($process_number=100, $day_buffer = 0, $require_reciprocal = 0, $failed_only = 0, $limit_start = null) {
        $query = '';
        if(!$this->PMDR->getConfig('reciprocal_field')) {
            $query = 'SELECT SQL_CALC_FOUND_ROWS id, user_id, www, require_reciprocal, www_status, www_reciprocal, www_date_checked FROM '.T_LISTINGS;
        } else {
            $query = 'SELECT SQL_CALC_FOUND_ROWS id, user_id, www, require_reciprocal, www_status, www_reciprocal, www_date_checked, '.$this->PMDR->getConfig('reciprocal_field').' FROM '.T_LISTINGS;
        }
        $where = array();
        if($require_reciprocal AND !$failed_only) {
            $where[] = 'require_reciprocal=1';
        }
        if($day_buffer) {
            $where[] = 'www_date_checked < DATE_SUB(NOW(),INTERVAL '.$day_buffer.' DAY)';
        }
        if($failed_only) {
            $where[] = '(www_status=0 OR (www_reciprocal=0 AND require_reciprocal=1))';
        }
        if(count($where)) {
            $query .= ' WHERE '.implode(' AND ',$where);
        }
        $query .= ' ORDER BY www_date_checked ASC LIMIT ';
        if(!is_null($limit_start)) {
            $query .= $this->db->Clean(intval($limit_start),false);
        }
        $query .= $this->db->Clean(intval($process_number),false);
        return $query;
    }

    /**
    * Check listing URLs
    * @param int $process_number Number of listings to process
    * @param int $day_buffer Number of days before a listing gets checked again
    * @param boolean $require_reciprocal Only check links requiring a reciprocal link
    */
    public function checkLinks($process_number=100, $day_buffer = 0, $require_reciprocal = 0, $failed_only = 0, $limit_start = null) {
        $links = $this->db->GetAll($this->buildLinksQuery($process_number, $day_buffer, $require_reciprocal, $failed_only, $limit_start));
        $link_results = array();
        $link_results['dead'] = 0;
        $link_results['valid'] = 0;
        $link_results['processed'] = 0;
        $link_results['reciprocal_valid'] = 0;
        $link_results['reciprocal_invalid'] = 0;
        $link_results['reciprocal_required_invalid'] = 0;

        foreach($links as $link) {
            $link_results['processed']++;
            $status = $this->checkURL($link['www']);
            if($link['www'] != '') {
                if($status == 'dead') {
                    $www_status = 0;
                    $link_results['dead']++;
                } else {
                    $www_status = 1;
                    $link_results['valid']++;
                }
            } else {
                $www_status = 1;
            }

            if(!$this->PMDR->getConfig('reciprocal_field')) {
                if($link['www'] != '') {
                    if($status != 'valid') {
                        $link_results['reciprocal_invalid']++;
                        if($link['require_reciprocal']) {
                            $link_results['reciprocal_required_invalid']++;
                        }
                        $www_reciprocal = 0;
                    } else {
                        $www_reciprocal = 1;
                        $link_results['reciprocal_valid']++;
                    }
                } else {
                    $www_reciprocal = 0;
                }
            } elseif($link[$this->PMDR->getConfig('reciprocal_field')] != '') {
                $status = $this->checkURL($link[$this->PMDR->getConfig('reciprocal_field')]);
                if($status != 'valid') {
                    $www_reciprocal = 0;
                    $link_results['reciprocal_invalid']++;
                    if($link['require_reciprocal']) {
                        $link_results['reciprocal_required_invalid']++;
                    }
                } else {
                    $www_reciprocal = 1;
                    $link_results['reciprocal_valid']++;
                }
            } else {
                $www_reciprocal = 0;
            }

            if($link['require_reciprocal'] == 1 AND $www_reciprocal == 0 AND ($link['www'] != '' OR $link[$this->PMDR->getConfig('reciprocal_field')] != '')) {
                $this->PMDR->get('Email_Templates')->send('admin_reciprocal_failed',array('listing_id'=>$link['id']));
                $this->PMDR->get('Email_Templates')->send('reciprocal_failed',array('to'=>$link['user_id'],'listing_id'=>$link['id']));
            }
            if($www_status == 0 AND $link['www'] != '') {
                $this->PMDR->get('Email_Templates')->send('admin_website_failed',array('listing_id'=>$link['id']));
            }
            $this->db->Execute("UPDATE ".T_LISTINGS." SET www_status=?, www_reciprocal=?, www_date_checked=NOW() WHERE id=?",array($www_status,$www_reciprocal,$link['id']));
        }

        return $link_results;
    }

    /**
    * Check a string to see if a link exists
    * @param string $link Link to search for
    * @param string $content String to check
    * @return boolean
    */
    public function hasLink($link, $content) {
        $link = preg_quote(rtrim($link, '/'), '/');
        // Remove all comments, don't want links in comments to be validated
        $content = preg_replace('/<!--.*-->/sU', '', $content);

        if(preg_match("/<a(.*)href=[\"']".$link."|".str_replace('www.','',$link)."(\/?)[\"'](.*)>(.+)<\/a>/si", $content)) {
            return true;
        } elseif(preg_match("/<script type=[\"']text\/javascript[\"'] src=[\"']".preg_quote(BASE_URL."/site_links.php?action=display&id=",'/')."\d*[\"']><\/script>/si", $content)) {
            return true;
        } else {
            return false;
        }
    }
}
?>