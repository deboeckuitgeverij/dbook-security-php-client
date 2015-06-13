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
     * Gates
     * @var string
     */
    const GATE_NONE   = false;
    const GATE_SSO    = 'sso';
    const GATE_OAUTH2 = 'oauth2';

    /**
     * methods
     * @var string
     */
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * Environements
     * @var string
     */
    const ENV_DEV     = 'dev-';
    const ENV_TEST    = 'test-';
    const ENV_PREPROD = 'preprod-';
    const ENV_PROD    = '';

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