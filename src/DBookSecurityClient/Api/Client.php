<?php
namespace DBookSecurityClient\Api;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\Model\Product;

/**
 * Helper class for broker of single sign-on
 */
class Client implements \DBookSecurityClient\UserInterface
{

    /**
     * methods
     * @var string
     */
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * Url of SSO server
     * @var string
     */
    public $url = "http://::env::dbook-security.deboeck.com/api/";

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
     * IP forced
     * @var string
     */
    protected $ip = null;

    /**
     * Get url
     *
     * @return string
     */
    protected function getUrl ()
    {
        return str_replace('::env::', $this->env, rtrim($this->url, '/'));
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
     * CALL the API in POST
     *
     * @param string $p_call
     * @param array  $p_datas
     * @param mixed  $p_statusCode
     */
    public function apiCall ($p_method = self::METHOD_GET, $p_call, $p_datas = array())
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
        if ($p_method != self::METHOD_GET) {
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
            echo '<h1>Error with request</h1><pre>' . print_r($body, true) . '</pre>';
            echo '<h2>><pre>' . print_r($ret, true) . '</h2>';
            throw new \Exception("SSO failure: HTTP request to server failed. " . curl_error($curl));
        }
        if ($body != '') {
            if (json_decode($body) === false || json_decode($body) === null) {
                echo '<h1>Error with Body</h1><pre>' . print_r($body, true) . '</pre>';
                throw new \Exception("SSO failure: HTTP request to server failed !");
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
    protected function parseInfo ($p_response)
    {
        $this->userinfo = false;
        //
        $result = json_decode($p_response);
        if (is_array($result)) {
            return $result;
        } else {
            if (is_object($result)) {
                return (array)$result;
            }
        }
        return null;
    }

    /**
     * Constructor
     * 
     * @param string  $p_broker
     * @param string  $p_secret
     * @param string  $p_ip
     * @param boolean $p_auto_attach
     */
    public function __construct ($p_broker=null, $p_secret=null, $p_ip=null, $p_auto_attach=true, $p_env=DBCST::ENV_DEV)
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
        $this->env = $p_env;
    }

    /**
     * Try to get a user with it's id
     *
     * @param string $p_id
     *
     * @return DBookSecurityClient\Model\User
     */
    public function getUserById ($p_id)
    {
        list($ret, $body) = $this->apiCall(self::METHOD_GET, '/users/id/' . $p_id);
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
     * @return DBookSecurityClient\Model\User
    */
    public function getUserByOauth2Token ($p_token)
    {
        list($ret, $body) = $this->apiCall(self::METHOD_GET, '/users/oauth2/' . $p_token);
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
     * @return DBookSecurityClient\Model\User
     */
    public function getOAuth2Token ($p_code, $p_redirect_uri = null)
    {
        list($ret, $body) = $this->apiCall(self::METHOD_POST, '/oauth2/token/' . $p_code, array('redirect_uri' => $p_redirect_uri));
        if ($ret == 200) {
            if (is_array($arr = $this->parseInfo($body))) {
                return $arr;
            }
        }
        return false;
    }

}
