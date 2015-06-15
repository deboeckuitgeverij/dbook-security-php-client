<?php
namespace DBookSecurityClient\Gate;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\Model\Product;
use DBookSecurityClient\DBookSecurityException;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Base
{

    /**
     * Gates' url
     * @var string
     */
    protected $url = "https://::env::dbook-security.deboeck.com/";

    /**
     * My identifier, given by SSO provider.
     * @var string
     */
    protected $broker = null;

    /**
     * My secret word, given by SSO provider.
     * @var string
     */
    protected $secret = null;

    /**
     * IP forced
     * @var string
     */
    protected $ip = null;

    /**
     * Default environment
     * @var string
     */
    protected $env = DBCST::ENV_DEV;

    /**
     * Field to exclude from query on redirect
     * @var array
     */
    protected $exclude_at_redirect = array();

    /**
     * Redirect uri
     * @var string
     */
    protected $uri = null;

    /**
     * Authorize calls
     * @var array
     */
    protected $calls = array();

    /**
     * Get url
     * 
     * @return string
     */
    protected function getUrl ($p_api = true)
    {
        $url = str_replace('::env::', $this->env, rtrim($this->url, '/'));
        if ($this->env == DBCST::ENV_DEV) {
            // No https in dev
            $url = str_replace('https://', 'http://', $url);
        }
        return $url;
    }

    /**
     * Create hmach string
     * 
     * @param mixed $p_datas
     * 
     * @return array
     */
    protected function hmacCreate ($p_datas)
    {
        $iRequestTime = time();
        $msgData      = is_array($p_datas) ? http_build_query($p_datas, '', '&') : $p_datas;
        $data         = $iRequestTime . $this->broker . $msgData;
        $serverHash   = hash_hmac('sha256', $data, $this->secret);
        
        return array (
            'API_ID'   => $this->broker,
            'API_TIME' => $iRequestTime,
            'API_HASH' => $serverHash
        );
    }

    /**
     * Get current url ithout technical fields
     * 
     * @param array $p_datas
     * 
     * @return string
     */
    protected function getCurrentUrl ($p_datas = array())
    {
        $url    = "http://{$_SERVER["SERVER_NAME"]}";
        $parts  = parse_url($_SERVER['REQUEST_URI']);
        $fields = array();
        if(array_key_exists('query', $parts) && $parts['query'] != '') {
            parse_str($parts['query'], $fields);
            foreach ($this->exclude_at_redirect as $key) {
                if (array_key_exists($key, $fields)) {
                    unset($fields[$key]);
                }
            }
        }
        $url    = $url . $parts['path'];
        $params = http_build_query(array_merge($fields, $p_datas), '', '&');
        if ($params !== '') {
            $url = $url . '?' . $params;
        }
        
        return $url;
    }

    /**
     * CALL the API in GET
     * 
     * @param string $p_call
     * @param array  $p_datas
     * @param mixed  $p_statusCode
     */
    protected function gateCall ($p_call, $p_datas = array(), $p_statusCode = null)
    {
        $url  = $this->getUrl();
        $call = '';
        $API  = array();
        if (array_key_exists($p_call, $this->calls)) {
            $API  = $this->hmacCreate($p_datas);
            $call = $this->calls[$p_call]['route'];
        } else {
            throw new DBookSecurityException(sprintf('Call %s doesn\'t exists !', $p_call));
        }
        $msgData = is_array($p_datas) ? http_build_query($p_datas, '', '&') : $p_datas;
        if ($msgData == '') {
            $msgData = http_build_query($API, '', '&');
        } else {
            $msgData = $msgData . '&' . http_build_query($API, '', '&');
        }
        $url = $url . $call . '?' . $msgData;
        header("Location: " . $url, true, $p_statusCode);
        exit;
    }

    /**
     * Add a route
     * 
     * @param string $p_name
     * @param string $p_route
     * @param string $p_method
     * @param array  $p_params
     * 
     * @return object
     */
    protected function addCall ($p_name, $p_route, $p_method = DBCST::METHOD_GET, $p_params = array())
    {
        $this->calls[$p_name] = array(
            'name'   => $p_name,
            'method' => $p_method,
            'route'  => $p_route,
            'params' => $p_params
        );
        
        return $this;
    }

    /**
     * Exclude a field from redirect uri
     *
     * @param string $p_field
     *
     * @return \DBookSecurityClient\SSO\Client
     */
    public function excludeFieldAtRedirect ($p_field)
    {
        $this->exclude_at_redirect[] = $p_field;
        
        return $this;
    }

    /**
     * Set redirect uri
     *
     * @param string $p_uri
     *
     * @return \DBookSecurityClient\Gate\OAuth2
     */
    public function setRedirectUri ($p_uri)
    {
        $this->uri = $p_uri;
    
        return $this;
    }

}