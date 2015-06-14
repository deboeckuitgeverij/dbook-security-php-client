<?php
namespace DBookSecurityClient\Models;

use DBookSecurity\Constants AS DBCST;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Token
{

    /**
     * Access Code
     * @var string
     */
    protected $access_code = null;

    /**
     * Refresh token
     * @var string
     */
    protected $refresh_token = null;

    /**
     * Expires in
     * @var integer
     */
    protected $expires_in = 0;

    /**
     * Type
     * @var string
     */
    protected $token_type = null;

    /**
     * Scope
     * @var string
     */
    protected $scope = null;

    /**
     * Constructor
     * 
     * @param mixed $p_record
     */
    public function __construct ($p_record = array())
    {
        if (is_array($p_record)) {
            if (array_key_exists('access_token', $p_record)) {
                $this->setAccessCode($p_record['access_token']);
            }
            if (array_key_exists('expires_in', $p_record)) {
                $this->setExpiresIn($p_record['expires_in']);
            }
            if (array_key_exists('token_type', $p_record)) {
                $this->setTokenType($p_record['token_type']);
            }
            if (array_key_exists('refresh_token', $p_record)) {
                $this->setRefreshToken($p_record['refresh_token']);
            }
            if (array_key_exists('scope', $p_record)) {
                $this->setScope($p_record['scope']);
            }
        }
    }

    /**
     * Set access code
     * 
     * @param string $p_access_code
     * 
     * @return \DBookSecurityClient\Models\Token
     */
    public function setAccessCode ($p_access_code)
    {
        $this->access_code = $p_access_code;
        
        return $this;
    }

    /**
     * Get access code
     * 
     * @return string
     */
    public function getAccessCode ()
    {
        return $this->access_code;
    }

    /**
     * Set refresh token
     * 
     * @param string $p_refresh_token
     * 
     * @return \DBookSecurityClient\Models\Token
     */
    public function setRefreshToken ($p_refresh_token)
    {
        $this->refresh_token = $p_refresh_token;
        
        return $this;
    }

    /**
     * Get the refresh token
     * 
     * @return string
     */
    public function getRefreshToken ()
    {
        return $this->refresh_token;
    }

    /**
     * Set expires in
     * 
     * @param integer $p_expires
     * 
     * @return \DBookSecurityClient\Models\Token
     */
    public function setExpiresIn ($p_expires)
    {
        $this->expires_in = $p_expires;
        
        return $this;
    }

    /**
     * Get expires in
     * 
     * @return integer
     */
    public function getExpiresIn ()
    {
        return $this->expires_in;
    }

    /**
     * Set type
     * 
     * @param string $p_type
     * 
     * @return \DBookSecurityClient\Models\Token
     */
    public function setTokenType ($p_type)
    {
        $this->token_type = $p_type;
        
        return $this;
    }

    /**
     * Get type
     * 
     * @return string
     */
    public function getTokenType ()
    {
        return $this->token_type;
    }

    /**
     * Set scope
     * 
     * @param string $p_scope
     * 
     * @return \DBookSecurityClient\Models\Token
     */
    public function setScope ($p_scope)
    {
        $this->scope = $p_scope;
        
        return $this;
    }

    /**
     * Get scope
     * @return string
     */
    public function getScope ()
    {
        return $this->scope;
    }

}