<?php
namespace DBookSecurityClient\Gate;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\Model\Product;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class OAuth2 extends Base
{

    /**
     * Constructor
     *
     * @param string  $p_broker
     * @param string  $p_secret
     * @param string  $p_ip
     * @param string  $p_env
     */
    public function __construct ($p_broker=null, $p_secret=null, $p_ip=null, $p_env=DBCST::ENV_DEV)
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
        $this
            ->addCall('login', '/auth/login')
        ;
    }

    /**
     * Get an authorizationCode for some scopes
     * 
     * @return string
     */
    public function askAuthorizationCode ($p_scopes = array(), $p_state = null)
    {
        $add = '';
        if ($p_state !== null) {
            $add = '&state=' . $p_state;
        }
        $continue = $this->getUrl() . '/auth/authorize?response_type=code&client_id=' . $this->broker . $add . '&redirect_uri=' . $this->uri;
        list($ret, $result) = $this->gateCall('login', array('continue' => $continue));
    }

    /**
     * Get an authorizationCode for some scopes
     *
     * @return string
     */
    public function getAuthorizationCode ()
    {
        return $_GET['code'];
    }

}