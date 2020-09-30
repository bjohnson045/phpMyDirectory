<?php
/**
* API
*/
class API {
    /**
     * HTTP request type: GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * The resource requested, example: /users
     */
    protected $resource = '';
    /**
     * Optional action, example: /users/getUserByEmail
     */
    protected $action = '';
    /**
     * Additional URL components used as arguements /<resource>/<action>/<arg0>/<arg1>
     */
    protected $args = array();
    /**
     * Input from PUT requests
     */
    protected $file = null;
    /**
    * API Key
    */
    protected $key;
    /**
    * API version, example: v1
    */
    protected $version;
    /**
    * Content type
    * @var string
    */
    protected $content_type = 'json';
    /**
    * Registry object
    * @var object
    */
    protected $PMDR;
    /**
    * Database object
    * @var object
    */
    protected $db;

    /**
    * API constructor
    * @param object $PMDR
    * @param string version
    * @param string $request
    * @param string $origin
    * @return API
    */
    public function __construct($PMDR, $parameters = array()) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->key = $this->PMDR->getConfig('api_key');
        $this->allowed_ip_addresses = preg_split("/[\r\n,]+/",$this->PMDR->getConfig('api_ip_addresses'),-1,PREG_SPLIT_NO_EMPTY);
        $this->version = $parameters['version'];

        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        if(!$this->authenticate()) {
            $this->_response('Access Denied',401);
            exit();
        }

        $this->args = explode('/', rtrim($parameters['request'], '/'));
        $this->resource = array_shift($this->args);
        if(array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->action = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        switch($this->method) {
            case 'DELETE':
                if(empty($this->action)) {
                    $this->action = 'delete';
                }
                $this->request = $this->PMDR->get('Cleaner')->clean_input($_POST);
                break;
            case 'POST':
                if(empty($this->action)) {
                    $this->action = 'post';
                }
                $this->request = $this->PMDR->get('Cleaner')->clean_input($_POST);
                break;
            case 'GET':
                if(empty($this->action)) {
                    $this->action = 'get';
                }
                $this->request = $this->PMDR->get('Cleaner')->clean_input($_GET);
                break;
            case 'PUT':
                if(empty($this->action)) {
                    $this->action = 'put';
                }
                $this->request = $this->PMDR->get('Cleaner')->clean_input($_GET);
                $this->file = file_get_contents("php://input");
                break;
            default:
                $this->_response('Invalid Method', 405);
                break;
        }
    }

    /**
    * Authenticate access to the API
    * Authenticates by IP address and the auth user and password
    * @return boolean True if authenticated
    */
    private function authenticate() {
        if(!defined('SECURITY_KEY') OR !in_array(get_ip_address(),$this->allowed_ip_addresses)) {
            return false;
        }
        if(!isset($_SERVER['PHP_AUTH_USER']) OR !isset($_SERVER['PHP_AUTH_PASS'])) {
            return false;
        }
        $user = $this->db->GetRow("SELECT u.id, api.username, api.password FROM ".T_USERS." u INNER JOIN ".T_USERS_API_KEYS." api
        ON u.id=.api.user_id WHERE api.username=?",array($_SERVER['PHP_AUTH_USER']));
        if(!$user OR $user['password'] != hash('sha256',$_SERVER['PHP_AUTH_PASS'].SECURITY_KEY)) {
            return false;
        }
        return true;
    }

    /**
    * Process an API call
    * @return string JSON response
    */
    public function process() {
        if(file_exists(PMDROOT.'/includes/api/'.$this->version.'/'.$this->resource.'.php')) {
            include(PMDROOT.'/includes/api/'.$this->version.'/'.$this->resource.'.php');
            $class_name = $this->resource.'_API';
            $resource_api = new $class_name($this->PMDR);
            if(method_exists($resource_api,$this->action)) {
                return $this->_response($resource_api->{$this->action}($this->args));
            }
        }
        return $this->_response('No resource:'.$this->resource, 404);
    }

    /**
    * Format a response
    * @param mixed $data Response data
    * @param int $status Status code to return
    * @return string Formatted response
    */
    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        $this->send_content_type($this->content_type);
        echo $this->content_type;
        switch($this->content_type) {
            case 'json':
                return json_encode($data);
                break;
            case 'javascript':
            case 'html':
                return $data;
                break;
        }
    }

    /**
    * Send a content type header
    * @param string $type The type of content type header to send
    * @return void|false False on invalid $type
    */
    public function send_content_type($type) {
        switch($type) {
            case 'json':
                header("Content-type: text/json; charset=".CHARSET);
                break;
            case 'xml':
                header("Content-type: text/xml; charset=".CHARSET);
                echo '<?xml version="1.0" encoding="'.CHARSET.'"?>';
                break;
            case 'rss':
            case 'javascript':
                header("Content-type: application/x-javascript");
                break;
            case 'html':
                header("Content-type: text/html; charset=".CHARSET);
                break;
            default:
                return false;
        }
    }

    /**
    * Convert a status code into a message
    * @param int $code
    * @return string Message
    */
    private function _requestStatus($code) {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code])?$status[$code]:$status[500];
    }
}
?>