<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Constants as DBCST;

class OAuth2Gate extends BaseGate
{

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
        $this->api  = new \DBookSecurityClient\Api\Client($p_broker_key, $p_broker_secret, null, $p_env);
        $this->gate = new \DBookSecurityClient\Gate\OAuth2($p_broker_key, $p_broker_secret, null, $p_env);
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
     * Get an authorizationCode for some scopes
     *
     * @return string
     */
    public function askAuthorizationCode ($p_scopes = array(), $p_state = null)
    {
        $this->gate->askAuthorizationCode($p_scopes, $p_state);
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
     * @param string $p_state
     *
     * @return string
     */
    public function getToken ($p_authorization_code, $p_state = null)
    {
        return $this->api->getOAuth2Token($p_authorization_code, $this->uri, $p_state);
    }

}