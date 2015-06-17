<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Constants as DBCST;

class StandardAuthGate extends BaseGate
{

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
     * Not authorized.... use ::getInstance()
     *
     * @param string  $p_broker_key
     * @param string  $p_broker_secret
     * @param string  $p_env
     * @param boolean $p_debug
     */
    protected function __construct ($p_broker_key, $p_broker_secret, $p_env, $p_debug)
    {
        $this->setDebug($p_debug);
        $this->gate = new \DBookSecurityClient\Gate\SSO($p_broker_key, $p_broker_secret, null, $p_env);
        $this->api = new \DBookSecurityClient\Api\Client($p_broker_key, $p_broker_secret, null, $p_env);
        $this->api
            ->addCookie('DBSV2APP', $this->gate->getSessionToken())
            ->addCookie('DBSV2CDSSO', $this->gate->getCDSSOID())
        ;
    }

    /**
     * Get Instance
     *
     * @param string  $p_broker_key
     * @param string  $p_broker_secret
     * @param string  $p_env
     * @param boolean $p_debug
     *
     * @return \DBookSecurity\DBookSecurityClient
     */
    public static function getInstance ($p_broker_key, $p_broker_secret, $p_env = DBCST::ENV_DEV, $p_debug = false)
    {
        if (self::$_instance === false) {
            self::$_instance = new self($p_broker_key, $p_broker_secret, $p_env, $p_debug);
        }
    
        return self::$_instance;
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

}