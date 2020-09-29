<?php
/**
* HTTP Request class
*/
class HTTP_Request {
    /**
    * Error message
    * @var string
    */
    var $error_message = null;

    /**
    * Error number
    * @var int
    */
    var $error_number = null;

    /**
    * HTTP response code of last curl request
    * @var int
    */
    var $response = null;

    /**
    * Settings for specific connection variables
    * @var array
    */
    var $settings = array();

    /**
    * Registry
    * @var object
    */
    var $PMDR;
    var $redirect = false;

    /**
    * HTTP Request Constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
    }

    /**
    * Get response/content from request
    * @param string $method Method for request (fopen, curl, or socket)
    * @param string $link URL to retrieve
    * @return mixed Content from request
    */
    function get($method, $link) {
        if($link == '') {
            return false;
        }

        switch($method) {
            case 'fopen':
                $content = $this->fopen($link);
                break;
            case 'curl':
                $content = $this->curl($link);
                break;
            case 'socket':
                $content = $this->curl($link);
                break;
            default:
                return false;
                break;
        }

        if($content == false) { return false; }

        return $content;
    }

    /**
    * Open link to get contents using file_get_contents
    * @param string $link URL to request
    * @return string
    */
    function fopen($link) {
        $content = @file_get_contents($link);
        return $content;
    }

    /**
    * Open link to get contents using CURL
    * @param string $link URL to request
    * @return mixed
    */
    function curl($link) {
        $ch = curl_init();
        @curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);

        if(ini_get('open_basedir') != '') {
            if(isset($this->settings[CURLOPT_FOLLOWLOCATION])) {
                $this->redirect = true;
                unset($this->settings[CURLOPT_FOLLOWLOCATION]);
            }
            if(isset($this->settings[CURLOPT_COOKIEFILE])) {
                unset($this->settings[CURLOPT_COOKIEFILE]);
            }
        }

        foreach($this->settings as $key=>$value) {
            if(!curl_setopt($ch, $key, $value)) {
                $constants = get_defined_constants(true);
                $lookup = preg_grep('/^CURLOPT_/', array_flip($constants['curl']));
                trigger_error('CURL Setopt failed. Key: "'.$lookup[$key].'", Value: "'.strval($value).'"',E_USER_WARNING);
                unset($constants,$lookup);
            }
        }

        if($this->PMDR->get('curl_proxy_url') != '') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY,$this->PMDR->get('curl_proxy_url'));
        }

        curl_setopt($ch, CURLOPT_REFERER, BASE_URL);

        $content = curl_exec($ch);

        if($this->error_number = curl_errno($ch)) {
            $this->error_message = curl_error($ch);
            curl_close($ch);
            return false;
        }

        $response = curl_getinfo($ch);
        curl_close($ch);

        if($this->redirect == true AND ($response['http_code'] == 302 OR $response['http_code'] == 301 OR $response['http_code'] == 303) AND !empty($response['redirect_url'])) {
            return $this->curl($response['redirect_url']);
        }

        $this->response = $response['http_code'];

        return $content;
    }

    /**
    * Open link to get contents using fsockopen
    * @param string $link URL to request
    * @return mixed
    */
    function socket($link) {
        // separate link into parts
        $linkParts = parse_url($link);

        // open a socket connection to the specified host and port
        $fp = @fsockopen($linkParts['host'], ($linkParts['port'] == 0) ? 80 : $linkParts['port'], $errno, $errstr, 5);

        if (!$fp) {
            // socket error
            $this->error_number = $errno;
            $this->error_message = $errstr;
            return false;
        } else {
            // build headers to be sent as a GET request
            $linkParts['path'] = trim($linkParts['path']) == '' ? '/' : $linkParts['path'];
            $out = "GET $linkParts[path] HTTP/1.1\r\n";
            $out .= "Host: $linkParts[host]\r\n";
            $out .= "User-agent: N/A\r\n";
            $out .= "Connection: Close\r\n\r\n";

            // write into the socket
            fwrite($fp, $out);

            $content = '';
            while (!feof($fp)) {
                // get response from server
                $content .= fgets($fp, 128);
            }

            fclose($fp);
            return $content;
        }
    }
}
?>