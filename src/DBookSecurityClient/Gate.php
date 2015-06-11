<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Constants;
use DBookSecurity\DBookSecurityException;
use DBookSecurity\ErrorCodes;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\Model\Product;

/**
 * Mais class, the Client
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Gate
{

    /**
     * Client
     * @var DBookSecurity\DBookSecurityClient
     */
    protected static $_instance = false;

    /**
     * Auth
     * @var DBookSecurityClient\AuthentificationInterface
     */
    protected static $_authentification = false;

    /**
     * Auth
     * @var DBookSecurityClient\AuthorizationInterface
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
     * @return \DBookSecurity\DBookSecurityClient
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
     * @param DBookSecurityClient\AuthentificationInterface $p_client
     * 
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setAuthenticationClient ($p_client)
    {
        if (!$p_client instanceof \DBookSecurityClient\AuthentificationInterface) {
            throw new DBookSecurityException('The Authentification interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHENTIFICATION_INTERFACE);
        }
        self::$_authentification = $p_client;
        return $this;
    }

    /**
     * Get connexion interface
     * 
     * @throws DBookSecurityException
     * 
     * @return \DBookSecurity\DBookSecurityClient\AuthentificationInterface
     */
    public function getAuthenticationClient ()
    {
        if (self::$_authentification instanceof \DBookSecurityClient\AuthentificationInterface) {
            return self::$_authentification;
        }
        throw new DBookSecurityException('The Authentification interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHENTIFICATION_INTERFACE);
    }

    /**
     * Set Authorization API
     * 
     * @param DBookSecurityClient\AuthorizationInterface $p_client
     * 
     * @return \DBookSecurity\DBookSecurityClient
     */
    public function setAuthorizationClient ($p_client)
    {
        if (!$p_client instanceof \DBookSecurityClient\AuthorizationInterface) {
            throw new DBookSecurityException('The Authorization interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHORIZATION_INTERFACE);
        }
        self::$_authorization = $p_client;
        return $this;
    }

    /**
     * Get Authorization interface
     * 
     * @throws DBookSecurityException
     * 
     * @return \DBookSecurity\DBookSecurityClient\AuthorizationInterface
     */
    public function getAuthorizationClient ()
    {
        if (self::$_authorization instanceof \DBookSecurityClient\AuthorizationInterface) {
            return self::$_authorization;
        }
        throw new DBookSecurityException('The Authorization interface is wrong !', ErrorCodes::ERROR_WRONG_AUTHORIZATION_INTERFACE);
    }

    /**
     * get user Infos
     *
     * @param string $p_redirectOnError
     *
     * @return User|boolean
     */
    public function getUser ($p_redirectOnError = Constants::REDIRECT_NONE)
    {
        $user = false;
        try {
            $client = $this->getAuthenticationClient();
            $user   = $client->getUser();
            if ($user === false) {
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
    public function checkLoggedIn ($p_redirectOnError = Constants::REDIRECT_NONE,
                                   $p_redirectOnSuccess = Constants::REDIRECT_NONE)
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
    public function isAuthenticated ($p_redirectOnError = Constants::REDIRECT_NONE)
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
                                             $p_redirectMode = Constants::REDIRECT_TO_HOME,
                                             $p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $client = $this->getAuthenticationClient();
            $result = $client->signinByLoginAndPassword($p_email, $p_password, $p_autoLogin);
            if (!$result) {
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
    public function logout ($p_redirectMode = Constants::REDIRECT_TO_LOGIN,
                            $p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $client = $this->getAuthenticationClient();
            $result = $client->completeLogout();
            if ($result) {
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
    public function completeLogout ($p_redirectMode = Constants::REDIRECT_TO_LOGIN,
                                    $p_redirectOnError = Constants::REDIRECT_NONE)
    {
        try {
            $client = $this->getAuthenticationClient();
            $result = $client->completeLogout();
            if ($result) {
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
    public function takeToken ($p_products, $p_redirectOnError = Constants::REDIRECT_NONE)
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
    public function freeToken ($p_products, $p_redirectOnError = Constants::REDIRECT_NONE)
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

}