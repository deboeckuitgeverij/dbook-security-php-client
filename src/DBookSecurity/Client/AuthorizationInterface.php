<?php
namespace DBookSecurity\Client;

use DBookSecurity\Constants;

/**
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
interface AuthorizationInterface
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
    public function checkAccess ($p_productCode, $p_accessMode = Constants::ACCESS_READ, $p_redirectMode = Constants::REDIRECT_NONE);

    /**
     * Get access list
     * 
     * @param string $p_productCode
     * 
     * @return array
     */
    public function getAccessList ($p_productCode, $p_ressourceCode);

}