<?php
namespace DBookSecurity\Client;

use DBookSecurity\Constants;

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
    public function checkLoggedIn ();

    /**
     * login with email and password
     *
     * @param string  $p_email
     * @param string  $p_password
     * @param boolean $p_autoLogin
     *
     * @return boolean
     */
    public function loginByEmailAndPassword ($p_email, $p_password, $p_autoLogin = false);

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

}