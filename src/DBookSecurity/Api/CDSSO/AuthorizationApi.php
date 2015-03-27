<?php
namespace DBookSecurity\Api\CDSSO;

use DBookSecurity\Constants;

/**
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class AuthorizationApi implements \DBookSecurity\Api\AuthorizationInterface
{

    /**
     * Check access
     *
     * @param string $p_productCode
     * @param string $p_accessMode
     * @param string $p_redirectMode
     *
     * @return this
     */
    public function checkAccess ($p_productCode, $p_accessMode = Constants::ACCESS_READ, $p_redirectMode = Constants::REDIRECT_NONE)
    {
        return $this;
    }

    /**
     * Get access list
     *
     * @param string $p_productCode
     *
     * @return array
     */
    public function getAccessList ($p_productCode, $p_ressourceCode)
    {
        return array(Constants::ACCESS_READ, Constants::ACCESS_ADD, Constants::ACCESS_MODIFY, Constants::ACCESS_DELETE);
    }

}