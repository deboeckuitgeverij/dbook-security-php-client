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

    /**
     * Audiences (products)
     * @var string
     */
    const AUDIENCE_ALL     = 'ALL';
    const AUDIENCE_TEACHER = 'TEACHER';
    const AUDIENCE_STUDENT = 'STUDENT';

    /**
     * Languages
     * @var string
     */
    const LANGUAGE_UNKNOWN = null;
    const LANGUAGE_FR      = 'FR';
    const LANGUAGE_NL      = 'NL';
    const LANGUAGE_EN      = 'EN';

    /**
     * Product modes
     * @var string
     */
    const PRODUCT_MODE_GROUP = 'GROUP';
    const PRODUCT_MODE_USER  = 'USER';

    /**
     * Product types
     * @var string
     */
    const PRODUCT_TYPE_STANDARD = 'STANDARD';
    const PRODUCT_TYPE_ABO      = 'ABO';
    const PRODUCT_TYPE_DEMO     = 'DEMO';

    /**
     * Product durations
     * @var string
     */
    const PRODUCT_DURATION_UNLIMITED = 'UNLIMITED';
    const PRODUCT_DURATION_1M        = '1M';
    const PRODUCT_DURATION_1Y        = '1Y';  // 1 year : 01/01 to 31/12
    const PRODUCT_DURATION_1SY       = '1SY'; // 1 school year

    /**
     * Get all audiences
     * 
     * @return array
     */
    public static function getAudiences ()
    {
        return array(self::AUDIENCE_STUDENT, self::AUDIENCE_TEACHER);
    }

    /**
     * Get all languages
     * 
     * @return array
     */
    public static function getLanguages ()
    {
        return array(self::LANGUAGE_EN, self::LANGUAGE_FR, self::LANGUAGE_NL);
    }

    /**
     * Return all products types
     * 
     * @return array
     */
    public static function getProductModes ()
    {
        return array(self::PRODUCT_MODE_GROUP, self::PRODUCT_MODE_USER);
    }

    /**
     * Return all types
     * 
     * @return array
     */
    public static function getProductTypes ()
    {
        return array(self::PRODUCT_TYPE_STANDARD, self::PRODUCT_TYPE_ABO, self::PRODUCT_TYPE_DEMO);
    }

    /**
     * Return all durations
     * 
     * @return array
     */
    public static function getProductDurations ()
    {
        return array(self::PRODUCT_DURATION_1M, self::PRODUCT_DURATION_1SY, self::PRODUCT_DURATION_1Y, self::PRODUCT_DURATION_UNLIMITED);
    }

    /**
     * Return default DBookSecurity base url
     * 
     * @param string $p_env
     * 
     * @return string
     */
    public static function getDBookSecurityBaseUrl ($p_env = self::ENV_DEV)
    {
        $url = '';
        switch ($p_env) {
            case self::ENV_PROD:
                return 'https://dbook-security.deboeck.com';
            case self::ENV_PREPROD:
                return 'https://preprod-dbook-security.deboeck.com';
            case self::ENV_TEST:
                return 'https://test-dbook-security.deboeck.com';
        }
        
        return 'http://dev-dbook-security.deboeck.com';
    }

}