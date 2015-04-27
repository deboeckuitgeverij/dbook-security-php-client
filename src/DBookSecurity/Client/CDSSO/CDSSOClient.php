<?php
namespace DBookSecurity\Client\CDSSO;

use DBookSecurity\Constants AS DBCST;
use DBookSecurity\Client\Model\User;
use DBookSecurity\Client\Model\Product;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class CDSSOClient implements \DBookSecurity\Client\AuthentificationInterface, \DBookSecurity\Client\AuthorizationInterface
{

    /**
     * Cookies names
     * @var string
     */
    const SESSION_CDSSOID = 'CDSSOSSID';
    const SESSION_TOKEN   = 'SSOTOKEN';

    /**
     * Pass 401 http response from the server to the client
     * @var boolean
     */
    protected $pass401 = false;

    /**
     * Pass 406 http response from the server to the client
     * @protected
     */
    protected $pass406 = false;

    /**
     * Url of SSO server
     * @var string
     */
    protected $url = "http://dev-cdsso-secure.deboeck.com/v1/cdsso";
    
    /**
     * My broker name, given by SSO provider.
     * @var string
     */
    protected $broker = null;

    /**
     * My secret word, given by SSO provider.
     * @var string
     */
    protected $secret = null;

    /**
     * Need to be shorter than session expire of SSO server
     * @var string
     */
    protected $sessionExpire = 1800;

    /**
     * Session hash
     * @var string
     */
    protected $sessionToken;

    /**
     * CDSSO
     * @var unknown
     */
    protected $cdsso;

    /**
     * User info received from the server.
     * @var array
     */
    protected $userinfo = null;

    /**
     * IP forced, VM's and public access
     * @var string
     */
    protected $ip = null; // protected $ip = '10.60.0.143';

    /**
     * Constructor
     */
    public function __construct ($p_url = null, $p_broker = null, $p_secret = null, $p_ip = null, $p_auto_attach = true)
    {
        if (!session_id()) {
            session_start();
        }
        if ($p_url !== null) {
            $this->url = $p_url;
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
        if (isset($_COOKIE[self::SESSION_TOKEN])) {
            $this->sessionToken = $_COOKIE[self::SESSION_TOKEN];
        }
        if (! isset($_COOKIE[self::SESSION_CDSSOID])) {
            if (isset($_GET[self::SESSION_CDSSOID])) {
                setcookie(self::SESSION_CDSSOID, $_GET[self::SESSION_CDSSOID]);
                $url = "http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}";
                preg_match('/\?' . self::SESSION_CDSSOID . '\=(.*)\&dummy\=1/', $_SERVER["REQUEST_URI"], $matches);
                if (is_array($matches) && count($matches) > 0) {
                    $url = str_replace($matches[0], '', $url);
                } 
                header("Location: " . $url, true, 307);
                exit();
            } else {
                $car = '?';
                if (strpos($_SERVER["REQUEST_URI"], '?') !== false) {
                    $car = '&';
                }
                $url = $this->getCdssoUrl() . "&redirect=" . urlencode('http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] . $car . self::SESSION_CDSSOID . '=:cdsso:&dummy=1');
                header("Location: " . $url, true, 307);
                exit();
            }
        }
        $this->attach($p_auto_attach);
    }

    /**
     * call attach
     */
    public function attach ($p_auto_attach = true)
    {
        if ($p_auto_attach && ! isset($this->sessionToken)) {
            $url = $this->getAttachUrl() . "&redirect=" . urlencode("http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}");
            header("Location: " . $url, true, 307);
            exit();
        }
    }

    /**
     * Get session token
     *
     * @return string
     */
    public function getSessionToken ()
    {
        if (! isset($this->sessionToken)) {
            $this->sessionToken = md5(uniqid(rand(), true));
            setcookie(self::SESSION_TOKEN, $this->sessionToken, time() + $this->sessionExpire);
        }
        return $this->sessionToken;
    }

    /**
     * Generate session id from session key
     *
     * @return string
     */
    protected function getSessionId ()
    {
        if (! isset($this->sessionToken)) {
            return null;
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($this->ip !== null) {
            $ip = $this->ip;
        }
        return "SSO-{$this->broker}-{$this->sessionToken}-" . md5('session' . $this->sessionToken . $ip . $this->secret);
    }

    /**
     * Generate session id from session key
     *
     * @return string
     */
    protected function getCdssoId ()
    {
        return $_COOKIE[self::SESSION_CDSSOID];
    }

    /**
     * Execute on SSO server.
     *
     * @param string $cmd
     * @param array  $vars
     *
     * @return array
     */
    protected function serverCmd ($p_cmd, $p_vars = null)
    {
        $curl = curl_init($this->url . '/' . urlencode($p_cmd));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIE, "PHPSESSID=" . $this->getSessionId() . ';CDSSOSSID=' . $this->getCdssoId());
        if (isset($p_vars)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $p_vars);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $body = curl_exec($curl);
        $ret  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl) != 0) {
            echo '<pre>' . print_r($body, true) . '</pre>';
            throw new \Exception("SSO failure: HTTP request to server failed. " . curl_error($curl));
        }
        if (json_decode($body) === false || json_decode($body) === null) {
            echo '<pre>' . print_r($body, true) . '</pre>';
            throw new \Exception("SSO failure: HTTP request to server failed !");
        }
        curl_close($curl);
        return array(
            $ret,
            $body
        );
    }

    /**
     * Get URL to attach session at SSO server
     *
     * @return string
     */
    public function getAttachUrl ()
    {
        $token = $this->getSessionToken();
        $ip    = $_SERVER['REMOTE_ADDR'];
        if ($this->ip !== null) {
            $ip = $this->ip;
        }
        $checksum = md5("attach{$token}{$ip}{$this->secret}");
        return "{$this->url}/attach?broker={$this->broker}&token=$token&checksum=$checksum";
    }

    /**
     * Get CDSSO url
     *
     * @return string
     */
    public function getCdssoUrl ()
    {
        return "{$this->url}/session?broker={$this->broker}";
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function checkLoggedIn ()
    {
        return $this->getInfo();
    }

    /**
     * login with email and password
     *
     * @param string  $p_email
     * @param string  $p_password
     * @param boolean $p_autoLogin
     *
     * @return boolean
     */
    public function loginByEmailAndPassword ($p_email, $p_password, $p_autoLogin = false)
    {
        $username = $p_email;
        $password = $p_password;
        list($ret, $body) = $this->serverCmd('login', array('login'=>$username, 'password'=>$password));
        switch ($ret) {
            case 200:
                $this->parseInfo($body);
                return true;
            case 401:
                if ($this->pass401) {
                    header("HTTP/1.1 401 Unauthorized");
                }
                break;
            default:
                //var_dump($body);die;
                throw new \Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
        }
        return false;
    }

    /**
     * Logout
     *
     * @return boolean
     */
    public function logout ()
    {
        return true;
    }

    /**
     * Logout
     *
     * @return boolean
     */
    public function completeLogout ()
    {
        list($ret, $body) = $this->serverCmd('logout');
        if ($ret != 200) {
            throw new \Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
        }
        setcookie(self::SESSION_TOKEN, null, time() - 1000);
        return true;
    }

    /**
     * Set user info from user XML
     *
     * @param string $p_response
     */
    protected function parseInfo ($p_response)
    {
        $result = json_decode($p_response);
        if ($result !== null && $result->_meta->status == 'SUCCESS') {
            $arr = (array) $result->records;
            // Init user object
            $this->userinfo = new User();
            foreach ($arr as $key => $value) {
                switch ($key) {
                    case DBCST::USER_ID:
                        $this->userinfo->setId($arr[$key]);
                        break;
                    case DBCST::USER_LOGIN:
                        $this->userinfo->setLogin($arr[$key]);
                        break;
                    case DBCST::USER_TITLE:
                        $this->userinfo->setTitle($arr[$key]);
                        break;
                    case DBCST::USER_LAST_NAME:
                        $this->userinfo->setLastname($arr[$key]);
                        break;
                    case DBCST::USER_FIRST_NAME:
                        $this->userinfo->setFirstname($arr[$key]);
                        break;
                    case DBCST::USER_EMAIL:
                        $this->userinfo->setEmail($arr[$key]);
                        break;
                    case DBCST::USER_ROLES:
                        $roles = $arr[$key];
                        foreach ($roles as $oneProduct) {
                            $aProduct = new Product();
                            $aProduct
                                ->setId($oneProduct->code)
                                ->setCode($oneProduct->code)
                                ->setFrom($oneProduct->from)
                                ->setTo($oneProduct->to)
                                ->setTokens($oneProduct->tokens)
                            ;
                            if ($oneProduct->type == '' || $oneProduct->type == '0' || $oneProduct->type === false) {
                                $aProduct->setType(Product::TYPE_USER);
                            } else {
                                $aProduct->setType(Product::TYPE_GROUP, $oneProduct->type);
                            }
                            $this->userinfo->addProduct($aProduct);
                        }
                        break;
                }
            }
            if (!$this->userinfo->isValid()) {
                $this->userinfo = false;
            }
        } else {
            $this->userinfo = false;
        }
    }

    /**
     * Set user info from user XML
     *
     * @param string $p_response
     */
    protected function parseTokens ($p_response)
    {
        $ret = array();
        // result is an array of products
        $result = json_decode($p_response);
        if ($result !== null && $result->_meta->status == 'SUCCESS') {
            $arr = (array) $result->records;
        }
        return $ret;
    }

    /**
     * Get user information.
     * 
     * @return User|boolean
     */
    public function getUser ()
    {
        if ($this->userinfo === null || $this->userinfo === false) {
            list ($ret, $body) = $this->serverCmd('info');
            switch ($ret) {
                case 200:
                    $this->parseInfo($body);
                    break;
                case 401:
                    if ($this->pass401) {
                        header("HTTP/1.1 401 Unauthorized");
                    }
                    $this->userinfo = false;
                    break;
                case 406:
                    // session destroyed, expired, ...
                    setcookie(self::SESSION_TOKEN, null, time() - 1000);
                    $this->sessionToken = null;
                    $this->userinfo     = false;
                    $this->attach(true);
                    break;
                default:
                    throw new \Exception("SSO failure: The server responded with a $ret status" . (! empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) . '".' : '.'));
            }
        }
        return $this->userinfo;
    }

    /**
     * Try to get a token per product
     *
     * @param array  $p_products
     *
     * @return array
     */
    public function takeToken ($p_products)
    {
        $result = array();
        // Call server
        list ($ret, $body) = $this->serverCmd('take', $p_products);
        switch ($ret) {
            case 200:
                $result = $this->parseTokens($body);
                break;
            default:
                throw new \Exception("SSO failure: The server responded with a $ret status" . (! empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) . '".' : '.'));
        }
        return $result;
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
        // Call server
        list ($ret, $body) = $this->serverCmd('free', $p_products);
        switch ($ret) {
            case 200:
                return true;
                break;
            default:
                throw new \Exception("SSO failure: The server responded with a $ret status" . (! empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) . '".' : '.'));
        }
        return false;
    }

}