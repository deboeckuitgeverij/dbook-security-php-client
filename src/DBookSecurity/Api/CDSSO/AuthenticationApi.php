<?php
namespace DBookSecurity\Api\CDSSO;

use DBookSecurity\Constants AS DBCST;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class AuthenticationApi implements \DBookSecurity\Api\AuthentificationInterface
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
    protected $userinfo;

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
                $url = $this->getCdssoUrl() . "&redirect=" . urlencode('http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] . '?' . self::SESSION_CDSSOID . '=:cdsso:&dummy=1');
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
                return 0;
            default:
                var_dump($body);die;
                throw new \Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
        }
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
            $this->userinfo['identity'] = $arr['id'];
            foreach ($arr as $key => $value) {
                if (in_array($key, array(DBCST::USER_ID, DBCST::USER_LOGIN, DBCST::USER_TITLE, DBCST::USER_LAST_NAME,
                                         DBCST::USER_FIRST_NAME, DBCST::USER_EMAIL, DBCST::USER_ROLES))) {
                    $this->userinfo[$key] = (string) $value;
                }
            }
        }
    }

    /**
     * Get user information.
     * 
     * @return array
     */
    public function getInfo ()
    {
        if (! isset($this->userinfo)) {
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
                    $this->userinfo = false;
                    $this->attach(true);
                    break;
                default:
                    throw new \Exception("SSO failure: The server responded with a $ret status" . (! empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) . '".' : '.'));
            }
        }
        return $this->userinfo;
    }

    /**
     * Ouput user information as XML
     */
    public function info ()
    {
        $this->getInfo();
        if (! $this->userinfo) {
            if ($this->pass401) {
                header("HTTP/1.0 401 Unauthorized");
                echo "Not logged in";
                exit();
            }
        }
        header('Content-type: text/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8" ?>', "\n";
        echo '<user identity="' . htmlspecialchars($this->userinfo['identity'], ENT_COMPAT, 'UTF-8') . '">', "\n";
        foreach ($this->userinfo as $key => $value) {
            if ($key == 'identity') {
                continue;
            }
            echo "<$key>", htmlspecialchars($value, ENT_COMPAT, 'UTF-8'), "</$key>", "\n";
        }
        echo '</user>';
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

}