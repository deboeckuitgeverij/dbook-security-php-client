<?php
namespace DBookSecurity\Client;

use DBookSecurity\Constants AS DBCST;;

/**
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
interface AuthentificationInterface
{

    /**
     * Check if user is logged in
     * 
     * @param string $p_redirectMode
     * 
     * @return boolean
     */
    public function checkLoggedIn ($p_redirectMode = DBCST::REDIRECT_NONE);

    /**
     * login with email and password
     *
     * @param string  $p_login
     * @param string  $p_password
     * @param boolean $p_autoLogin
     *
     * @return boolean
     */
    public function signinByLoginAndPassword ($p_login, $p_password, $p_autoLogin = false);

    /**
     * Logout
     * 
     * @return boolean
     */
    public function logout ();

    /**
     * Logout
     *
     * @return boolean
     */
    public function completeLogout ();

    /**
     * Get user information.
     * 
     * @return User|boolean
     */
    public function getUser ();

}