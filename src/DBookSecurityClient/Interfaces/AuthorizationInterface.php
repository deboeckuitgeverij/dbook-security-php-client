<?php
namespace DBookSecurityClient\Interfaces;

use DBookSecurityClient\Constants;

/**
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
interface AuthorizationInterface
{

    /**
     * Try to get a token per product
     * 
     * @param array $p_products
     * 
     * @return array
     */
    public function takeToken ($p_products);

    /**
     * Free products tokens
     *
     * @param array  $p_products
     *
     * @return boolean
     */
    public function freeToken ($p_products);

}