<?php
namespace DBookSecurityClient\SSO;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\Model\Product;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Client implements \DBookSecurityClient\AuthentificationInterface, \DBookSecurityClient\AuthorizationInterface
{

    /**
     * CDSSOID cookie name
     * @var string
     */
    const CDSSOID_NAME  = 'BROKER_CDSSOID';
    const CDSSOID_FIELD = 'CDSSOSSID';
    const TOKEN_NAME    = 'BROKER_TOKEN';
    const LOGOUT_FIELD  = 'logout';

    /**
     * Pass 401 http response of the server to the client
     */
    public $pass401=false;

    /**
     * Url of SSO server
     * @var string
     */
    public $url = "http://dev-api-dbook-security.deboeck.com/";
    
    /**
     * My identifier, given by SSO provider.
     * @var string
     */
    public $broker = null;

    /**
     * My secret word, given by SSO provider.
     * @var string
     */
    public $secret = null;

    /**
     * Need to be shorter than session expire of SSO server
     * @var string
     */
    public $sessionExpire = 1800;
    
    /**
     * Session hash
     * @var string
     */
    protected $sessionToken;

    /**
     * 
     * @var unknown
     */
    protected $cdsso;

    /**
     * User info recieved from the server.
     * @var array
     */
    protected $userinfo;

    /**
     * IP forced
     * @var string
     */
    protected $ip = null;

    /**
     * Create hmach string
     * 
     * @param mixed $p_datas
     * 
     * @return array
     */
    protected function hmacCreate ($p_datas)
    {
        $iRequestTime = time();
        $msgData      = is_array($p_datas) ? http_build_query($p_datas, '', '&') : $p_datas;
        $data         = $iRequestTime . $this->broker . $msgData;
        $serverHash   = hash_hmac('sha256', $data, $this->secret);
        return array (
            'API_ID'   => $this->broker,
            'API_TIME' => $iRequestTime,
            'API_HASH' => $serverHash
        );
    }

    /**
     * Get session token
     *
     * @return string
     */
    protected function getSessionToken()
    {
        if (!isset($this->sessionToken)) {
            $this->sessionToken = md5(uniqid(rand(), true));
            setcookie(self::TOKEN_NAME, $this->sessionToken, time() + $this->sessionExpire);
        }
        return $this->sessionToken;
    }

    /**
     * Return CDSSO cookie
     * 
     * @return string
     */
    protected function getCDSSOID ()
    {
        if (isset($_COOKIE[self::CDSSOID_NAME])) {
            return $_COOKIE[self::CDSSOID_NAME];
        }
        $this->reload();
    }

    /**
     * Get current url ithout technical fields
     * 
     * @return string
     */
    protected function getCurrentUrl ($p_datas = array())
    {
        $url    = "http://{$_SERVER["SERVER_NAME"]}";
        $parts  = parse_url($_SERVER['REQUEST_URI']);
        $fields = array();
        parse_str($parts['query'], $fields);
        if (array_key_exists(self::CDSSOID_FIELD, $fields)) {
            unset($fields[self::CDSSOID_FIELD]);
        }
        if (array_key_exists(self::LOGOUT_FIELD, $fields)) {
            unset($fields[self::LOGOUT_FIELD]);
        }
        $url    = $url . $parts['path'];
        $params = http_build_query(array_merge($fields, $p_datas), '', '&');
        if ($params !== '') {
            $url = $url . '?' . $params;
        }
        return $url;
    }

    /**
     * CALL the API in GET
     * 
     * @param string $p_call
     * @param array  $p_datas
     * @param mixed  $p_statusCode
     */
    protected function apiCallGet ($p_call, $p_datas = array(), $p_statusCode = null)
    {
        $url  = rtrim($this->url, '/');
        $call = '';
        $API  = array();
        switch (strtoupper($p_call)) {
            case 'CDSSO':
                $API = $this->hmacCreate($p_datas);
                $call = '/cdsso';
                break;
            case 'ATTACH':
                $API = $this->hmacCreate($p_datas);
                $call = '/attach';
                break;
            default:
                echo '<h1>ERROR, wrong call</h1>';
                exit;
        }
        $msgData = is_array($p_datas) ? http_build_query($p_datas, '', '&') : $p_datas;
        if ($msgData == '') {
            $msgData = http_build_query($API, '', '&');
        } else {
            $msgData = $msgData . '&' . http_build_query($API, '', '&');
        }
        $url = $url . $call . '?' . $msgData;
        header("Location: " . $url, true, $p_statusCode);
        exit;
    }

    /**
     * CALL the API in GET
     *
     * @param string $p_call
     * @param array  $p_datas
     * @param mixed  $p_statusCode
     */
    protected function apiCallPost ($p_call, $p_datas = array())
    {
        $url  = rtrim($this->url, '/');
        $call = '';
        $API  = array();
        switch (strtoupper($p_call)) {
            case 'USER':
                $API = $this->hmacCreate($p_datas);
                $call = '/user';
                break;
            case 'LOGIN':
                $API = $this->hmacCreate($p_datas);
                $call = '/login';
                break;
            case 'LOGOUT':
                $API = $this->hmacCreate($p_datas);
                $call = '/logout';
                break;
            default:
                echo '<h1>ERROR, wrong call</h1>';
                exit;
        }
        // Convert API to Header array
        $headers = array();
        foreach ($API as $key=>$value) {
            $headers[] = $key . ' : ' . $value;
        }
        // Next
        $url = $url . $call;
        // Curll init and call...
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_COOKIE, "DBSV2APP=" . $this->getSessionToken() . ';DBSV2CDSSO=' . $this->getCDSSOID());
        if (isset($p_datas)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $p_datas);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $body = curl_exec($curl);
        $ret  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl) != 0) {
            echo '<h1>Error with request</h1><pre>' . print_r($body, true) . '</pre>';
            throw new Exception("SSO failure: HTTP request to server failed. " . curl_error($curl));
        }
        if ($body != '') {
            if (json_decode($body) === false || json_decode($body) === null) {
                echo '<h1>Error with Body</h1><pre>' . print_r($body, true) . '</pre>';
                throw new Exception("SSO failure: HTTP request to server failed !");
            }
        } else {
            $body = null;
        }
        curl_close($curl);
        return array($ret, $body);
    }

    /**
     * Set user info from user XML
     *
     * @param string $p_response
     */
    protected function parseUser ($p_response)
    {
        $this->userinfo = false;
        $arr            = false;
        //
        $result = json_decode($p_response);
        if (is_array($result)) {
            $arr = $result;
        } else {
            if (is_object($result)) {
                $arr = (array)$result;
            }
        }
        if ($arr !== false) {
            $this->userinfo = new User($arr);
        }
    }

    /**
     * Set user info from user XML
     *
     * @param string $p_response
     */
    protected function parseInfo ($p_response)
    {
        $this->userinfo = false;
        //
        $result = json_decode($p_response);
        if (is_array($result)) {
            $this->userinfo = $result;
        } else {
            if (is_object($result)) {
                $this->userinfo = (array)$result;
            }
        }
    }

    /**
     * Constructor
     * 
     * @param string  $p_broker
     * @param string  $p_secret
     * @param string  $p_ip
     * @param boolean $p_auto_attach
     */
    public function __construct ($p_broker=null, $p_secret=null, $p_ip=null, $p_auto_attach=true)
    {
        session_start();
        if ($p_broker !== null) {
            $this->broker = $p_broker;
        }
        if ($p_secret !== null) {
            $this->secret = $p_secret;
        }
        if ($p_ip !== null) {
            $this->ip = $p_ip;
        }
        if (isset($_COOKIE[self::TOKEN_NAME])) {
            $this->sessionToken = $_COOKIE[self::TOKEN_NAME];
        }
        if (!isset($_COOKIE[self::CDSSOID_NAME])) {
            if (isset($_GET[self::CDSSOID_FIELD])) {
                setcookie(self::CDSSOID_NAME, $_GET[self::CDSSOID_FIELD]);
            } else {
                $uri = $this->getCurrentUrl(array(self::CDSSOID_FIELD => ':CDSSOID:'));
                $data = array('redirect_uri' => $uri);
                $this->apiCallGet('cdsso', $data, 307);
            }
        }
        $this->attach($p_auto_attach);
    }

    /**
     * call attach
     * 
     * @param boolean $p_auto_attach
     */
    public function attach ($p_auto_attach=true)
    {
        if ($p_auto_attach && !isset($this->sessionToken)) {
            $uri = $this->getCurrentUrl();
            $data = array('token'=>$this->getSessionToken(), 'redirect_uri' => $uri);
            $this->apiCallGet('attach', $data, 307);
            exit;
        }
    }

    /**
     * Reload page for all stuff, CDSSO, attach, ...
     * 
     * @todo infinite loop
     */
    public function reload ()
    {
        setcookie(self::CDSSOID_NAME, null, time() - 1000);
        setcookie(self::TOKEN_NAME, null, time() - 1000);
        $url = $this->getCurrentUrl();
        header("Location: " . $url, true, 301);
        exit;
    }

    /**
     * Check if user is logged in
     * 
     * @param string $p_redirectMode
     * 
     * @return boolean
     */
    public function checkLoggedIn ($p_redirectMode = DBCST::REDIRECT_NONE)
    {
        $this->getUser();
        if ($this->userinfo === false) {
            return false;
        }
        return true;
    }

    /**
     * login with email and password
     *
     * @param string  $p_login
     * @param string  $p_password
     * @param boolean $p_autoLogin
     *
     * @return boolean
     */
    public function signinByLoginAndPassword ($p_login, $p_password, $p_autoLogin = false)
    {
        if (!isset($p_login) && isset($_REQUEST['username'])) {
            $p_login=$_REQUEST['username'];
        }
        if (!isset($p_password) && isset($_REQUEST['password'])) {
            $p_password=$_REQUEST['password'];
        }
        list($ret, $body) = $this->apiCallPost('login', array('login'=>$p_login, 'password'=>$p_password));
        switch ($ret) {
            case 200:
                $this->parseInfo($body);
                return true;
                break;
            case 500:
                throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
            default:
                if ($this->pass401) {
                    header("HTTP/1.1 401 Unauthorized");
                }
                return false;
                break;
        }
    }
    
    /**
     * Logout
     * 
     * @return boolean
     */
    public function logout ()
    {
        list($ret, $body) = $this->apiCallPost('logout');
        if ($ret != 200) {
            throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
        }
        $this->reload();
        return true;
    }

    /**
     * Logout
     *
     * @return boolean
     */
    public function completeLogout ()
    {
        return $this->logout();
    }

    /**
     * Get user information.
     */
    public function getUser ()
    {
        if (!isset($this->userinfo)) {
            list($ret, $body) = $this->apiCallPost('user');
            switch ($ret) {
                case 200:
                    $this->parseUser($body);
                    break;
                case 401:
                    if ($this->pass401) {
                        header("HTTP/1.1 401 Unauthorized");
                    }
                    $this->userinfo = false;
                    break;
                case 500:
                    throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
                default:
                    // session destroyed, expired, ...
                    $this->reload();
                    break;
            }
        }
        return $this->userinfo;
    }
    /**
     * Try to get a token per product
     *
     * @param array $p_products
     *
     * @return array
     */
    public function takeToken ($p_products)
    {
        
    }
    
    /**
     * Free products tokens
     *
     * @param array  $p_products
     *
     * @return boolean
    */
    public function freeToken ($p_products)
    {
        
    }
}