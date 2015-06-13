<?php
namespace DBookSecurityClient\Interfaces;

use DBookSecurityClient\Constants;
use DBookSecurityClient\Models\User;

/**
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
interface UserInterface
{

    /**
     * Try to get a user with it's id
     * 
     * @param string $p_id
     * 
     * @return DBookSecurityClient\Models\User
     */
    public function getUserById ($p_id);

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string  $p_token
     *
     * @return DBookSecurityClient\Models\User
     */
    public function getUserByOauth2Token ($p_token);

}