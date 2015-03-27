<?php
namespace DBookSecurity;

use DBookSecurity\Constants;
use DBookSecurity\DBookSecurityException;
use DBookSecurity\ErrorCodes;

/**
 * Mais class, the API
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class DBookSecurityApi
{

    /**
     * API
     * @var DBookSecurity\DBookSecurityApi
     */
    protected static $_instance = false;

    /**
     * API
     * @var DBookSecurity\Api\AuthentificationInterface
     */
    protected static $_authentification = false;

    /**
     * API
     * @var DBookSecurity\Api\AuthorizationInterface
     */
    protected static $_authorization = false;

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
     * Not authorized.... use ::getInstance()
     * 
     * @param boolean $p_debug
     */
    protected function __construct ($p_debug = false)
    {
        $this->debug = false;
        if ($p_debug === true || $p_debug === '1' || $p_debug === 1) {
            $this->debug = true;
        }
    }

    /**
     * Get Instance
     * 
     * @param boolean $p_debug
     * 
     * @return \DBookSecurity\DBookSecurityApi
     */
    public static function getInstance ($p_debug = false)
    {
        if (self::$_instance === false) {
            self::$_instance = new self($p_debug);
        }
        return self::$_instance;
    }

    /**
     * Application Code
     *
     * @param string $p_code
     *
     * @return \DBookSecurity\DBookSecurityApi
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
     * @return \DBookSecurity\DBookSecurityApi
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
     * @return \DBookSecurity\DBookSecurityApi
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
     * @return \DBookSecurity\DBookSecurityApi
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
     * @return \DBookSecurity\DBookSecurityApi
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
    public function redirectTo ($p_redirectMode = Constants::REDIRECT_NONE)
    {
        switch ($p_redirectMode) {
            case Constants::REDIRECT_TO_LOGIN:
                self::redirectToUrl($this->loginUrl);
                break;
            case Constants::REDIRECT_TO_HOME:
                self::redirectToUrl($this->homeUrl);
                break;
        }
    }

    /**
     * Set Authentification API
     * 
     * @param DBookSecurity\Api\AuthentificationInterface $p_api
     * 
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function setAuthenticationApi ($p_api)
    {
        if (!$p_api instanceof \DBookSecurity\Api\AuthentificationInterface) {
            throw new DBookSecurityException('The Authentification interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHENTIFICATION_INTERFACE);
        }
        self::$_authentification = $p_api;
        return $this;
    }

    /**
     * Get connexion interface
     * 
     * @throws DBookSecurityException
     * 
     * @return \DBookSecurity\DBookSecurity\Api\AuthentificationInterface
     */
    public function getAuthenticationApi ()
    {
        if (self::$_authentification instanceof \DBookSecurity\Api\AuthentificationInterface) {
            return self::$_authentification;
        }
        throw new DBookSecurityException('The Authentification interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHENTIFICATION_INTERFACE);
    }

    /**
     * Set Authorization API
     * 
     * @param DBookSecurity\Api\AuthorizationInterface $p_api
     * 
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function setAuthorizationApi ($p_api)
    {
        if (!$p_api instanceof \DBookSecurity\Api\AuthorizationInterface) {
            throw new DBookSecurityException('The Authorization interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHORIZATION_INTERFACE);
        }
        self::$_authorization = $p_api;
        return $this;
    }

    /**
     * Get Authorization interface
     * 
     * @throws DBookSecurityException
     * 
     * @return \DBookSecurity\DBookSecurity\Api\AuthorizationInterface
     */
    public function getAuthorizationInterface ()
    {
        if (self::$_authorization instanceof \DBookSecurity\Api\AuthorizationInterface) {
            return self::$_authorization;
        }
        throw new DBookSecurityException('The Authorization interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHORIZATION_INTERFACE);
    }

    /**
     * get user Infos
     *
     * @param string  $p_redirectOnError
     *
     * @return array
     */
    public function getInfos ($p_redirectOnError = Constants::REDIRECT_NONE)
    {
        $infos = array();
        try {
            $api   = $this->getAuthenticationApi();
            $infos = $api->getInfo();
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        return $infos;
    }

    /**
     * Check if user is logged in
     *
     * @param string $p_redirectMode
     * @param string $p_redirectOnSuccess
     *
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function checkLoggedIn ($p_redirectOnError = Constants::REDIRECT_NONE,
                                   $p_redirectOnSuccess = Constants::REDIRECT_NONE)
    {
        try {
            $api    = $this->getAuthenticationApi();
            $result = $api->checkLoggedIn();
            if (!$result) {
                $this->redirectTo($p_redirectOnError);
            } else {
                $this->redirectTo($p_redirectOnSuccess);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        return $this;
    }

    /**
     * Is authenticatde ?
     * 
     * @param string  $p_redirectOnError
     * 
     * @return boolean
     */
    public function isAuthenticated ($p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $api = $this->getAuthenticationApi();
            return $api->checkLoggedIn();
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        return $this;
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
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function loginByEmailAndPassword ($p_email, $p_password, $p_autoLogin = false,
                                             $p_redirectMode = Constants::REDIRECT_TO_HOME,
                                             $p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $api    = $this->getAuthenticationApi();
            $result = $api->loginByEmailAndPassword($p_email, $p_password, $p_autoLogin);
            if (!$result) {
                // Need to send error...
                $this->redirectTo($p_redirectOnError);
            } else {
                $this->redirectTo($p_redirectMode);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        return $this;
    }

    /**
     * Logout
     *
     * @param string  $p_redirectMode
     * @param string  $p_redirectOnError
     *
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function logout ($p_redirectMode = Constants::REDIRECT_TO_LOGIN,
                            $p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $api    = $this->getAuthenticationApi();
            $result = $api->completeLogout();
            if ($result) {
                $this->redirectTo($p_redirectMode);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        return $this;
    }

    /**
     * Logout
     *
     * @param string  $p_redirectMode
     * @param string  $p_redirectOnError
     *
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function completeLogout ($p_redirectMode = Constants::REDIRECT_TO_LOGIN,
                                    $p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $api    = $this->getAuthenticationApi();
            $result = $api->completeLogout();
            if ($result) {
                $this->redirectTo($p_redirectMode);
            }
        } catch (\Exception $ex) {
            $this->redirectTo($p_redirectOnError);
        }
        return $this;
    }

    /**
     * Check access
     *
     * @param string $p_productCode
     * @param string $p_accessMode
     * @param string $p_redirectMode
     *
     * @return \DBookSecurity\DBookSecurityApi
     */
    public function checkAccess ($p_productCode, $p_accessMode = Constants::ACCESS_READ, $p_redirectMode = Constants::REDIRECT_NONE)
    {
        return $this;
    }

    /**
     * Get access list
     *
     * @param string $p_productCode
     * @param string $p_ressourceCode
     *
     * @return array
     */
    public function getAccessList ($p_productCode, $p_ressourceCode)
    {
        return array(Constants::ACCESS_READ, Constants::ACCESS_ADD, Constants::ACCESS_MODIFY, Constants::ACCESS_DELETE);
    }

    /**
     * Handle
     */
    public function handle ()
    {
        die('We are goging to check, ...');
    }

}