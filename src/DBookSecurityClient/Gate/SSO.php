<?php
namespace DBookSecurityClient\Gate;

use DBookSecurityClient\Api\Client;

class SSO extends Gate
{
    const CDSSOID_NAME  = 'BROKER_CDSSOID';
    const CDSSOID_FIELD = 'CDSSOID';
    const TOKEN_NAME    = 'BROKER_TOKEN';
    const LOGOUT_FIELD  = 'logout';

    /**
     * Pass 401 http response of the server to the client
     */
    protected $pass401 = false;

    /**
     * Need to be shorter than session expire of SSO server
     * @var int
     */
    public $sessionExpire = 1800;
    
    /**
     * Session hash
     * @var string
     */
    protected $sessionToken;

    /**
     * @param string  $broker
     * @param string  $secret
     * @param boolean $autoAttach
     * @param string  $env
     */
    public function __construct($broker, $secret, $env = self::ENV_PROD, $autoAttach = true)
    {
        parent::__construct($broker, $secret);

        if (isset($_COOKIE[self::TOKEN_NAME])) {
            $this->sessionToken = $_COOKIE[self::TOKEN_NAME];
        }
        if (isset($_GET[self::CDSSOID_FIELD])) {
            setcookie(self::CDSSOID_NAME, $_GET[self::CDSSOID_FIELD]);
        }

        $this->client
            ->addCookie('DBSV2APP', $this->getSessionToken())
            ->addCookie('DBSV2CDSSO', $this->getCDSSOID())
        ;
        
        // Special parameters
        $this
            ->addIgnoreQueryParam(self::CDSSOID_FIELD)
            ->addIgnoreQueryParam(self::LOGOUT_FIELD)
        ;

        if ($autoAttach) {
            $this->attach();
        }
    }

    /**
     * Get session token
     *
     * @return string
     */
    public function getSessionToken()
    {
        if (!isset($this->sessionToken)) {
            $this->sessionToken = md5(uniqid(rand(), true));
            setcookie(self::TOKEN_NAME, $this->sessionToken, time() + $this->sessionExpire);
        }
        return $this->sessionToken;
    }

    /**
     * return CDSSO cookie
     *
     * @return string|null
     */
    public function getCDSSOID()
    {
        if (isset($_COOKIE[self::CDSSOID_NAME])) {
            return $_COOKIE[self::CDSSOID_NAME];
        }

        return null;
    }

    public function authenticate(array $options = array())
    {
        $options = array_merge(array(
            'login' => isset($_REQUEST['login']) ? $_REQUEST['login']: null,
            'password' => isset($_REQUEST['password']) ? $_REQUEST['password']: null,
        ), $options);

        return $this->call(Client::METHOD_POST, '/login', array(
            'login' => $options['login'],
            'password' => $options['password'],
        ));
    }

    public function logout(array $options = array())
    {
        $options = array_merge(array(
            'login' => isset($_REQUEST['login']) ? $_REQUEST['login']: null,
            'password' => isset($_REQUEST['password']) ? $_REQUEST['password']: null,
        ), $options);

        return $this->call(Client::METHOD_POST, '/login', array(
            'login' => $options['login'],
            'password' => $options['password'],
        ));
    }

    public function attach()
    {
        if (!isset($_COOKIE[self::CDSSOID_NAME] ) || !isset($this->sessionToken)) {
            $this->forwardToGate('/auth/attach', array(
                'token' => $this->getSessionToken(),
                'redirect_uri' => $this->getCurrentUrl(array(
                    self::CDSSOID_FIELD => ':CDSSOID:'
                ))
            ));
        }
    }
}