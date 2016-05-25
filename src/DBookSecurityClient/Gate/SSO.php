<?php
namespace DBookSecurityClient\Gate;

use DBookSecurityClient\Constants AS DBCST;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class SSO extends Base
{

    /**
     * CDSSOID cookie name
     * @var string
     */
    const CDSSOID_NAME  = 'BROKER_CDSSOID';
    const CDSSOID_FIELD = 'CDSSOSSID';
    const TOKEN_NAME    = 'BROKER_TOKEN';
    const LOGOUT_FIELD  = 'logout';

    /**
     * Pass 401 http response of the server to the client
     */
    protected $pass401 = false;

    /**
     * Need to be shorter than session expire of SSO server
     * @var string
     */
    public $sessionExpire = 1800;
    
    /**
     * Session hash
     * @var string
     */
    protected $sessionToken;

    /**
     * Constructor
     * 
     * @param string  $p_broker
     * @param string  $p_secret
     * @param string  $p_ip
     * @param boolean $p_auto_attach
     * @param string  $p_env
     */
    public function __construct ($p_broker=null, $p_secret=null, $p_ip=null, $p_env=DBCST::ENV_DEV, $p_auto_attach=true)
    {
        if (!session_id()) {
            session_start();
        }
        $this->env = $p_env;
        if ($p_broker !== null) {
            $this->broker = $p_broker;
        }
        if ($p_secret !== null) {
            $this->secret = $p_secret;
        }
        if ($p_ip !== null) {
            $this->ip = $p_ip;
        }
        if (isset($_COOKIE[self::TOKEN_NAME])) {
            $this->sessionToken = $_COOKIE[self::TOKEN_NAME];
        }
        if (isset($_GET[self::CDSSOID_FIELD])) {
            setcookie(self::CDSSOID_NAME, $_GET[self::CDSSOID_FIELD]);
        }
        // Special parameters
        $this
            ->excludeFieldAtRedirect(self::CDSSOID_FIELD)
            ->excludeFieldAtRedirect(self::LOGOUT_FIELD)
        ;
        // Available calls
        $this
            ->addCall('cdsso', '/cdsso')
            ->addCall('attach', '/auth/attach')
        ;
        // attach
        $this->attach($p_auto_attach);
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
     * Return CDSSO cookie
     *
     * @return string
     */
    public function getCDSSOID ()
    {
        if (isset($_COOKIE[self::CDSSOID_NAME])) {
            return $_COOKIE[self::CDSSOID_NAME];
        }
        $this->reload();
    }

    /**
     * call attach
     * 
     * @param boolean $p_auto_attach
     */
    public function attach ($p_auto_attach=true)
    {
        if ($p_auto_attach && (!isset($_COOKIE[self::CDSSOID_NAME] ) || !isset($this->sessionToken))) {
            $uri  = $this->getCurrentUrl(array(self::CDSSOID_FIELD => ':CDSSOID:'));
            $data = array('token'=>$this->getSessionToken(), 'redirect_uri' => $uri);
            $this->gateCall('attach', $data, 307);
            exit;
        }
    }

    /**
     * Reload page for all stuff, CDSSO, attach, ...
     *
     * @todo infinite loop
     */
    public function reload ()
    {
        setcookie(self::CDSSOID_NAME, null, time() - 1000);
        //setcookie(self::TOKEN_NAME, null, time() - 1000); We don't need to remove this one...
        $url = $this->getCurrentUrl();
        header("Location: " . $url, true, 301);
        exit;
    }

}