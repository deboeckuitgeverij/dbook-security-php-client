<?php
namespace DBookSecurityClient;

/**
 * NameSpace Constants
 * 
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Constants
{

    /**
     * Redirect constants
     * @var string
     */
    const REDIRECT_NONE     = 'NONE';
    const REDIRECT_TO_LOGIN = 'LOGIN';
    const REDIRECT_TO_HOME  = 'HOME';

    /**
     * Access mode constants
     * @var string
     */
    const ACCESS_READ   = 'READ';
    const ACCESS_ADD    = 'ADD';
    const ACCESS_MODIFY = 'MODIFY';
    const ACCESS_DELETE = 'DELETE';

    /**
     * User fields
     * @var string
     */
    const USER_ID         = 'id';
    const USER_FIRST_NAME = 'firstname';
    const USER_LAST_NAME  = 'lastname';
    const USER_TITLE      = 'title';
    const USER_LOGIN      = 'login';
    const USER_EMAIL      = 'email';
    const USER_ROLES      = 'roles';

}