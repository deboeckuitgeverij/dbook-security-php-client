<?php
namespace DBookSecurityClient\Api;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Interfaces\AuthentificationInterface;
use DBookSecurityClient\Interfaces\AuthorizationInterface;
use DBookSecurityClient\Interfaces\UserInterface;
use DBookSecurityClient\Models\User;
use DBookSecurityClient\Models\Product;
use DBookSecurityClient\Models\Token;
use DBookSecurityClient\DBookSecurityException;

/**
 * Main Api Client
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 * 
 * 
 */
class Client implements AuthentificationInterface, AuthorizationInterface, UserInterface
{

    /**
     * Api server url
     * @var string
     */
    protected $url = "https://::env::dbook-security.deboeck.com/api/";

    /**
     * My identifier, given by DeBoeck.
     * @var string
     */
    protected $broker = null;

    /**
     * My secret word, given by DeBoeck.
     * @var string
     */
    protected $secret = null;

    /**
     * IP forced
     * @var string
     */
    protected $ip = null;

    /**
     * Cookies
     * @var array
     */
    protected $cookies = array();

    /**
     * User cache
     * @var mixed
     */
    protected $userinfo = false;

    /**
     * Get url
     *
     * @return string
     */
    protected function getUrl ()
    {
        $url = str_replace('::env::', $this->env, rtrim($this->url, '/'));
        if ($this->env == DBCST::ENV_DEV) {
            // No https in dev
            $url = str_replace('https://', 'http://', $url);
        }
        return $url;
    }

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
     * Flush all cookies
     */
    public function flushCookies ()
    {
        $this->cookies = array();
        
        return $this;
    }

    /**
     * Add a Cookie
     * 
     * @param string $p_key
     * @param string $p_value
     * 
     * @return \DBookSecurityClient\Api\Client
     */
    public function addCookie ($p_key, $p_value)
    {
        $this->cookies[$p_key] = $p_value;
        
        return $this;
    }

    /**
     * Return the cookies' array as header
     * 
     * @return string | boolean
     */
    protected function getCookiesAsHeader()
    {
        $cookies = false;
        foreach ($this->cookies as $key=>$value)
        {
            if ($cookies === false) {
                $cookies = urlencode($key) . '=' . urlencode($value);
            } else {
                $cookies .= ';' . urlencode($key) . '=' . urlencode($value);
            }
        }
        
        return $cookies;
    }

    /**
     * CALL the API in POST, get the status and body
     *
     * @param string $p_call
     * @param array  $p_datas
     * @param mixed  $p_statusCode
     * 
     * @return array
     */
    public function apiCall ($p_method = DBCST::METHOD_GET, $p_call, $p_datas = array())
    {
        $url  = $this->getUrl();
        $call = '';
        $call = '/' . strtolower(ltrim($p_call, '/'));
        $API  = $this->hmacCreate($p_datas);
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
        if (false !== ($cookies = $this->getCookiesAsHeader())) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookies);
        }
        if ($p_method != DBCST::METHOD_GET) {
            curl_setopt($curl, CURLOPT_USERPWD, urlencode($this->broker) . ':' . urlencode($this->secret));
            curl_setopt($curl, CURLOPT_POST, true);
            if (isset($p_datas)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $p_datas);
            }
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $body = curl_exec($curl);
        $ret  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl) != 0) {
            throw new DBookSecurityException(sprintf('SSO failure: HTTP request to server failed. %s', curl_error($curl)));
        }
        if ($body != '') {
            if (json_decode($body) === false || json_decode($body) === null) {
                throw new DBookSecurityException('SSO failure: HTTP request to server failed !');
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
     * 
     * @return mixed
     */
    protected function parseInfo ($p_response)
    {
        $result = json_decode($p_response);
        if (is_array($result)) {
            
            return $result;
        } else {
            if (is_object($result)) {
                
                return (array)$result;
            }
        }
        
        return $p_response;
    }

    /**
     * Constructor
     * 
     * @param string  $p_broker
     * @param string  $p_secret
     * @param string  $p_ip
     * @param boolean $p_auto_attach
     */
    public function __construct ($p_broker=null, $p_secret=null, $p_ip=null, $p_env=DBCST::ENV_DEV)
    {
        if (!session_id()) {
            session_start();
        }
        if ($p_broker !== null) {
            $this->broker = $p_broker;
        }
        if ($p_secret !== null) {
            $this->secret = $p_secret;
        }
        if ($p_ip !== null) {
            $this->ip = $p_ip;
        }
        $this->env = $p_env;
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
     * @return array
     */
    public function signinByLoginAndPassword ($p_login, $p_password, $p_autoLogin = false)
    {
        if (!isset($p_login) && isset($_REQUEST['username'])) {
            $p_login=$_REQUEST['username'];
        }
        if (!isset($p_password) && isset($_REQUEST['password'])) {
            $p_password=$_REQUEST['password'];
        }
        list($ret, $body) = $this->apiCall(DBCST::METHOD_POST, 'login/' . $p_login . '/' . $p_password);
        if ($ret == 200) {
            
            return array($ret, $this->parseInfo($body));
        }
        
        return array($ret, $body);
    }

    /**
     * Logout
     *
     * @return array
     */
    public function logout ()
    {
        list($ret, $body) = $this->apiCall(DBCST::METHOD_POST, 'logout');
        
        return array($ret, $this->parseInfo($body));
    }

    /**
     * Logout
     *
     * @return array
     */
    public function completeLogout ()
    {
        return $this->logout();
    }

    /**
     * Get user information.
     * 
     * @return array
     */
    public function getUser ()
    {
        if (!$this->userinfo) {
            $this->userinfo = false;
            list($ret, $body) = $this->apiCall(DBCST::METHOD_POST, 'user');
            if ($ret == 200) {
                if (false !== ($datas = $this->parseInfo($body))) {
                    $this->userinfo = new User($datas);
                } else {
                    $ret = 500;
                }
            }
        } else {
            $ret = 200;
        }
        
        return array($ret, $this->userinfo);
    }

    /**
     * Try to get a token per product
     *
     * @param array $p_products
     *
     * @return string | boolean
     */
    public function takeToken ($p_products)
    {
        return false;
    }

    /**
     * Free products tokens
     *
     * @param array  $p_products
     *
     * @return string | boolean
     */
    public function freeToken ($p_products)
    {
        return false;
    }

    /**
     * Try to get a user with it's id
     *
     * @param string $p_id
     *
     * @return DBookSecurityClient\Models\User | boolean
     */
    public function getUserById ($p_id)
    {
        list($ret, $body) = $this->apiCall(DBCST::METHOD_GET, '/users/id/' . $p_id);
        if ($ret == 200) {
            if (is_array($arr = $this->parseInfo($body))) {
                
                return new User($arr);
            }
        }
        
        return false;
    }

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string  $p_token
     *
     * @return DBookSecurityClient\Models\User | boolean
    */
    public function getUserByOauth2Token ($p_token)
    {
        list($ret, $body) = $this->apiCall(DBCST::METHOD_GET, '/users/oauth2/' . $p_token);
        if ($ret == 200) {
            if (is_array($arr = $this->parseInfo($body))) {
                return new User($arr);
            }
        }
        
        return false;
    }

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string  $p_token
     *
     * @return DBookSecurityClient\Models\Token
     */
    public function getOAuth2Token ($p_code, $p_redirect_uri = null)
    {
        list($ret, $body) = $this->apiCall(DBCST::METHOD_POST, '/oauth2/token/' . $p_code, array('redirect_uri' => $p_redirect_uri));
        if ($ret == 200) {
            if (is_array($arr = $this->parseInfo($body))) {
                return new Token($arr);
            }
        }
        
        return false;
    }

}
