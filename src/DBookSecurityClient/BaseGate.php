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
class BaseGate
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
     * Home Page Url
     * @var string
     */
    protected $homeUrl = null;    

    /**
     * Login Page Url
     * @var string
     */
    protected $loginUrl = null;
    
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
     * Not authorized....
     *
     * @param string  $p_broker_key
     * @param string  $p_broker_secret
     * @param string  $p_env
     * @param boolean $p_debug
     */
    protected function __construct ($p_broker_key, $p_broker_secret, $p_env, $p_debug)
    {
    }

    /**
     * Set debug mdoe
     * 
     * @param boolean $p_debug
     */
    public function setDebug ($p_debug = false)
    {
        $this->debug = false;
        if ($p_debug === true || $p_debug === '1' || $p_debug === 1) {
            $this->debug = true;
        }
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
     * @param string $p_redirectOn
     */
    protected function redirectTo ($p_redirectOn = DBCST::REDIRECT_NONE)
    {
        switch ($p_redirectOn) {
            case DBCST::REDIRECT_TO_HOME:
                self::redirectToUrl($this->homeUrl);
                break;
            case DBCST::REDIRECT_TO_LOGIN:
                self::redirectToUrl($this->loginUrl);
                break;
        }
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
     * @return \DBookSecurityClient\Gate\...
     */
    public function setRedirectUri ($p_uri)
    {
        $this->uri = $p_uri;
        if ($this->gate !== null) {
            $this->gate->setRedirectUri($p_uri);
        } else {
            // @todo ...
        }
    
        return $this;
    }

}