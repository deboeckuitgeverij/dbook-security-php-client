<?php
namespace DBookSecurityClient\Gate;

use DBookSecurityClient\Api\Client;
use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Api\Client as Api;
use DBookSecurityClient\Model\Token;
use DBookSecurityClient\UriHelper;

class OAuth2 extends Gate
{
    /**
     * @var string
     */
    protected $redirectUri = null;

    /**
     * Constructor
     *
     * @param string $broker
     * @param string $secret
     * @param string $env
     * @param string|null $redirectUri
     */
    public function __construct ($broker, $secret, $env = self::ENV_DEV, $redirectUri = null)
    {
        parent::__construct($broker, $secret);

        $this->redirectUri = $redirectUri;
    }

    public function call($method = Client::METHOD_GET, $path, $data = array(), $headers = array())
    {
        $data['client_id'] = $this->broker;
        $data['client_secret'] = $this->secret;

        return parent::call($method, $path, $data, $headers);
    }

    /**
     * @param array $options
     * @return string
     */
    public function authenticate(array $options = array())
    {
        $options = array_merge(array(
            'scopes' => array(),
            'state' => null,
        ), $options);

        $params = array(
            'response_type' => 'code',
            'client_id' => $this->broker,
            'redirect_uri' => $this->redirectUri
        );

        if ($options['state'] !== null) {
            $params['state'] = $options['state'];
        }

        $continue = UriHelper::setQuery(
            UriHelper::addPath($this->getUrl(), '/auth/authorize'),
            $params
        );

        $this->forwardToGate('/auth/login', array('continue' => $continue));
    }

    /**
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $_GET['code'];
    }

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string $code
     * @param string $redirectUri
     * @param string $state
     *
     * @return Token|boolean
     */
    public function getOAuth2Token($code, $redirectUri = null, $state = null)
    {
        if (!$redirectUri) {
            $redirectUri = $this->redirectUri;
        }
        
        $data = array(
            'code' => $code,
            'redirect_uri' => $redirectUri
        );

        if ($state !== null) {
            $data['state'] = $state;
        }

        $response = $this->call(Client::METHOD_POST, '/oauth2/token', $data);

        if ($response->getStatus() === 200) {
            return new Token($response->getContent());
        }

        return false;
    }

    /**
     * Try to get a new token from a refreshToken
     *
     * @param string $refreshToken
     * @param string $state
     *
     * @return Token|boolean
     */
    public function getOAuth2FreshToken($refreshToken, $state = null)
    {
        $data = array(
            'code' => $refreshToken,
        );

        if ($state !== null) {
            $data['state'] = $state;
        }

        $response = $this->call(Client::METHOD_POST, '/oauth2/refresh', $data);
        if ($response->getStatus() === 200) {
            return new Token($response->getContent());
        }

        return false;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;

        return $this;
    }
}