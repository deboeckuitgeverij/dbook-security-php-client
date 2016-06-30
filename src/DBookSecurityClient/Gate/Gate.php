<?php
namespace DBookSecurityClient\Gate;

use DBookSecurityClient\Api\Client;
use DBookSecurityClient\Api\Response;
use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Api\Client as Api;
use DBookSecurityClient\ApiException;
use DBookSecurityClient\UriHelper;

abstract class Gate
{
    const ENV_DEV     = 'dev-';
    const ENV_TEST    = 'test-';
    const ENV_PREPROD = 'preprod-';
    const ENV_PROD    = '';
    
    /**
     * gate url
     *
     * @var string
     */
    protected $url = "https://::env::dbook-security.deboeck.com/";

    /**
     * @var string
     */
    protected $env = self::ENV_DEV;

    /**
     * broker identifier, given by SSO provider
     *
     * @var string
     */
    protected $broker = null;

    /**
     * broker key, given by SSO provider
     *
     * @var string
     */
    protected $secret = null;

    /**
     * fields to exclude from query on redirect
     *
     * @var array
     */
    protected $ignoredQueryParameters = array();

    /**
     * @var Client
     */
    protected $client;
    
    /**
     * @param string $broker
     * @param string $secret
     * @param string $env
     */
    public function __construct($broker, $secret, $env = self::ENV_DEV)
    {
        $this->broker = $broker;
        $this->secret = $secret;
        $this->env = $env;

        $this->client = new Client($env);
    }

    /**
     * @return string
     */
    public function getBroker()
    {
        return $this->broker;
    }

    /**
     * create hmac string
     * 
     * @param string|array $data
     * @return array
     */
    protected function createHmac($data)
    {
        $time           = time();
        $stringData     = is_array($data) ? http_build_query($data, '', '&') : $data;
        $serverHash     = hash_hmac(
            'sha256',
            $time . $this->broker . $stringData,
            $this->secret
        );
        
        return array (
            'API_ID'   => $this->broker,
            'API_TIME' => $time,
            'API_HASH' => $serverHash
        );
    }

    /**
     * @param array $data
     * @param array $headers
     * @return array
     */
    public function getSecurityHeaders($data = array(), &$headers = array())
    {
        foreach ($this->createHmac($data) as $key=>$value) {
            $headers[] = $key . ': ' . $value;
        }

        return $headers;
    }

    /**
     * exclude a field from redirect uri
     *
     * @param string $name
     * @return $this
     */
    public function addIgnoreQueryParam($name)
    {
        $this->ignoredQueryParameters[] = $name;

        return $this;
    }

    /**
     * get GATE url for configured environment
     *
     * @return string
     */
    protected function getUrl()
    {
        $url = str_replace('::env::', $this->env, $this->url);

        if ($this->env == self::ENV_DEV) {
            // No https in dev
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }

    /**
     * get current url without the self::$ignoredQueryParameters
     * optional $data passed will be added to the query string
     * 
     * @param array $data
     * @return string
     */
    protected function getCurrentUrl($data = array())
    {
        return UriHelper::getCurrentUrl($data, $this->ignoredQueryParameters);
    }

    /**
     * Redirect to the gate
     *
     * @param string $path
     * @param array $data
     * @param mixed $statusCode
     * @throws ApiException
     */
    protected function forwardToGate($path, $data = array(), $statusCode = 307)
    {
        $hmac = $this->createHmac($data);

        $url  = UriHelper::setQuery(
            UriHelper::addPath($this->getUrl(), $path),
            UriHelper::buildQuery($hmac, $data)
        );
        
        header("Location: " . $url, true, $statusCode);
        exit;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return \DBookSecurityClient\Api\Response
     * @throws ApiException
     */
    public function call($method = Client::METHOD_GET, $path, $data = array(), $headers = array())
    {
        return $this->client->execute($method, $path, $data, $this->getSecurityHeaders($data, $headers));
    }

    /**
     * @param array $options
     * @return mixed|Response
     */
    abstract public function authenticate(array $options = array());
}