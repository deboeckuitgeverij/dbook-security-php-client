<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Constants;
use DBookSecurityClient\Model\User;

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
     * @return DBookSecurityClient\Model\User
     */
    public function getUserById ($p_id);

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string  $p_token
     *
     * @return DBookSecurityClient\Model\User
     */
    public function getUserByOauth2Token ($p_token);

}