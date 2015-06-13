<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Constants as DBCST;
use DBookSecurity\DBookSecurityException;
use DBookSecurity\ErrorCodes;
use DBookSecurityClient\Models\User;
use DBookSecurityClient\Models\Product;

/**
 * Main class, the Client
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Gate
{

    /**
     * Instance
     * @var DBookSecurityClient\Gate
     */
    protected static $_instance = false;

    /**
     * Gate Client
     * @var DBookSecurityClient\Gate\...
     */
    protected $gate = null;

    /**
     * api
     * @var DBookSecurityClient\Api\Client
     */
    protected $api = null;

    /**
     * Application Name
     * @var string
     */
    protected $appName = null;

    /**
     * Application Code
     * @var string
     */
    protected $appCode = null;

    /**
     * Login Page Url
     * @var string
     */
    protected $loginUrl = null;

    /**
     * Home Page Url
     * @var string
     */
    protected $homeUrl = null;

    /**
     * Redirect to Login if Not Logged in ??
     * @var boolean
     */
    protected $redirectToLogin = false;

    /**
     * Debug mode
     * @var boolean
     */
    protected $debug = false;

    /**
     * Redirection uri
     * @var string
     */
    protected $uri = null;

    /**
     * Not authorized.... use ::getInstance()
     * 
     * @param string  $p_broker_key
     * @param string  $p_broker_secret
     * @param string  $p_gate_type
     * @param string  $p_env
     * @param boolean $p_debug
     */
    protected function __construct ($p_broker_key, $p_broker_secret, $p_gate_type, $p_env, $p_debug)
    {
        $this->debug = false;
        if ($p_debug === true || $p_debug === '1' || $p_debug === 1) {
            $this->debug = true;
        }
        switch ($p_gate_type) {
            case DBCST::GATE_SSO:
                $this->gate = new \DBookSecurityClient\Gate\SSO($p_broker_key, $p_broker_secret, null, $p_env);
                break;
            case DBCST::GATE_OAUTH2:
                $this->gate = new \DBookSecurityClient\Gate\OAuth2($p_broker_key, $p_broker_secret, null, $p_env);
                break;
        }
        $this->api = new \DBookSecurityClient\Api\Client($p_broker_key, $p_broker_secret, null, $p_env);
        if ($this->gate instanceof \DBookSecurityClient\Gate\SSO) {
            $this->api
                ->addCookie('DBSV2APP', $this->gate->getSessionToken())
                ->addCookie('DBSV2CDSSO', $this->gate->getCDSSOID())
            ;
        }
    }

    /**
     * Get Instance
     * 
     * @param string  $p_broker_key
     * @param string  $p_broker_secret
     * @param boolean $p_with_sso
     * @param boolean $p_debug
     * 
     * @return \DBookSecurity\DBookSecurityClient
     */
    public static function getInstance ($p_broker_key, $p_broker_secret, $p_gate_type = DBCST::GATE_NONE, 
                                        $p_env = DBCST::ENV_DEV, $p_debug = false)
    {
        if (self::$_instance === false) {
            self::$_instance = new self($p_broker_key, $p_broker_secret, $p_gate_type, $p_env, $p_debug);
        }
        
        return self::$_instance;
    }

    /**
     * Application Code
     *
     * @param string $p_code
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setAppCode ($p_code)
    {
        $this->appCode = $p_code;
        
        return $this;
    }

    /**
     * Set Application Name
     *
     * @param string $p_name
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setAppName ($p_name)
    {
        $this->appName = $p_name;
        
        return $this;
    }

    /**
     * Set Login Url
     *
     * @param string $p_url
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setLoginUrl ($p_url)
    {
        $this->loginUrl = $p_url;
        
        return $this;
    }

    /**
     * Set Home Url
     *
     * @param string $p_url
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setHomeUrl ($p_url)
    {
        $this->homeUrl = $p_url;
        
        return $this;
    }

    /**
     * Redirect to login
     *
     * @param boolean $p_redirect
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setRedirectToLoginIfNotLoggedIn ($p_redirect = true)
    {
        $this->redirectToLogin = false;
        if ($p_redirect === true) {
            $this->redirectToLogin = true;
        }
        
        return $this;
    }

    /**
     * Redirect to specific url
     *
     * @param string $p_url
     */
    protected static function redirectToUrl ($p_url)
    {
        header('location: ' . $p_url);
        exit();
    }

    /**
     * Redirect to ??
     *
     * @param string $p_redirectMode
     */
    public function redirectTo ($p_redirectMode = DBCST::REDIRECT_NONE)
    {
        switch ($p_redirectMode) {
            case DBCST::REDIRECT_TO_LOGIN:
                self::redirectToUrl($this->loginUrl);
                break;
            case DBCST::REDIRECT_TO_HOME:
                self::redirectToUrl($this->homeUrl);
                break;
        }
    }

    /**
     * get user Infos
     *
     * @param string $p_redirectOnError
     *
     * @return User|boolean
     */
    public function getUser ($p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        $user = false;
        try {
            list($status, $user) = $this->api->getUser();
            if ($status != 200) {
                $this->redirectTo($p_redirectOnError);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return $user;
    }

    /**
     * Check if user is logged in
     *
     * @param string $p_redirectMode
     * @param string $p_redirectOnSuccess
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function checkLoggedIn ($p_redirectOnError = DBCST::REDIRECT_NONE,
                                   $p_redirectOnSuccess = DBCST::REDIRECT_NONE)
    {
        try {
            $client = $this->getAuthenticationClient();
            $result = $client->checkLoggedIn();
            if (!$result) {
                $this->redirectTo($p_redirectOnError);
            } else {
                $this->redirectTo($p_redirectOnSuccess);
                
                return true;
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return false;
    }

    /**
     * Is authenticated ?
     * 
     * @param string  $p_redirectOnError
     * 
     * @return boolean
     */
    public function isAuthenticated ($p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        try {
            $client = $this->getAuthenticationClient();
            
            return $client->checkLoggedIn();
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return false;
    }

    /**
     * login with email and password
     *
     * @param string  $p_email
     * @param string  $p_password
     * @param boolean $p_autoLogin
     * @param string  $p_redirectMode
     * @param string  $p_redirectOnError
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function signinByLoginAndPassword ($p_email, $p_password, $p_autoLogin = false,
                                             $p_redirectMode = DBCST::REDIRECT_NONE,
                                             $p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        try {
            list($status, $result) = $this->api->signinByLoginAndPassword($p_email, $p_password, $p_autoLogin);
            if ($status != 200) {
                // Need to send error...
                $this->redirectTo($p_redirectOnError);
            } else {
                $this->redirectTo($p_redirectMode);
                
                return true;
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return false;
    }

    /**
     * Logout
     *
     * @param string  $p_redirectMode
     * @param string  $p_redirectOnError
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function logout ($p_redirectMode = DBCST::REDIRECT_NONE,
                            $p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        try {
            list($status, $result) = $this->api->logout();
            if ($status == 200) {
                $this->redirectTo($p_redirectMode);
                
                return true;
            } else {
                $this->redirectTo($p_redirectOnError);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return false;
    }

    /**
     * Logout
     *
     * @param string  $p_redirectMode
     * @param string  $p_redirectOnError
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function completeLogout ($p_redirectMode = DBCST::REDIRECT_TO_LOGIN,
                                    $p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        try {
            list($status, $result) = $this->api->completeLogout();
            if ($status == 200) {
                $this->redirectTo($p_redirectMode);
                
                return true;
            } else {
                $this->redirectTo($p_redirectOnError);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return false;
    }

    /**
     * Try to get one token per product
     * 
     * @param array  $p_products
     * @param string $p_redirectOnError
     * 
     * @return array
     */
    public function takeToken ($p_products, $p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        $result = array();
        try {
            $client = $this->getAuthorizationClient();
            $products = '';
            foreach ($p_products as $key=>$value) {
                if ($products == '') {
                    $products = $key;
                } else {
                    $products = $products . ';' . $key;
                }
            }
            $result = $client->takeToken(array('products' => $products));
            if ($result === false) {
                $this->redirectTo($p_redirectOnError);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return $result;
    }

    /**
     * Free products token
     *
     * @param array  $p_products
     * @param string $p_redirectOnError
     *
     * @return array
     */
    public function freeToken ($p_products, $p_redirectOnError = DBCST::REDIRECT_NONE)
    {
        $result = array();
        try {
            $client = $this->getAuthorizationClient();
            $result = $client->freeToken($p_products);
            if ($result !== false) {
                
                return true;
            } else {
                $this->redirectTo($p_redirectOnError);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        
        return false;
    }

    /**
     * Set redirect uri
     *
     * @param string $p_uri
     *
     * @return \DBookSecurityClient\Gate\OAuth2
     */
    public function setRedirectUri ($p_uri)
    {
        $this->uri = $p_uri;
        $this->gate->setRedirectUri($p_uri);
    
        return $this;
    }

    /**
     * Get an authorizationCode for some scopes
     *
     * @return string
     */
    public function askAuthorizationCode ($p_scopes = array())
    {
        $this->gate->askAuthorizationCode($p_scopes);
    }

    /**
     * Get an authorizationCode for some scopes
     *
     * @return string
     */
    public function getAuthorizationCode ()
    {
        return $this->gate->getAuthorizationCode();
    }

    /**
     * Get an token
     * 
     * @param string $p_authorization_code
     *
     * @return string
     */
    public function getToken ($p_authorization_code)
    {
        return $this->api->getOAuth2Token($p_authorization_code, $this->uri);
    }

}