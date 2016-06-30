<?php
namespace DBookSecurityClient\Api;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Gate\Gate;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\ApiException;
use DBookSecurityClient\UriHelper;

class Client
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string
     */
    protected $env;

    /**
     * Api server url
     * @var string
     */
    protected $url = "https://::env::dbook-security.deboeck.com/api/";

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * Cookies
     * @var array
     */
    protected $cookies = array();

    /**
     * User cache
     * @var User
     */
    protected $userInfo;

    /**
     * @param string $env
     */
    public function __construct($env = Gate::ENV_PROD)
    {
        $this->env = $env;
    }

    /**
     * @return Gate
     */
    public function getGate()
    {
        return $this->gate;
    }

    /**
     * get API url for configured environment
     *
     * @param null $path
     * @return string
     */
    protected function getUrl($path = null)
    {
        $url = str_replace('::env::', $this->env, rtrim($this->url, '/'));

        if ($this->env == Gate::ENV_DEV) {
            // No https in dev
            $url = str_replace('https://', 'http://', $url);
        }

        if ($path) {
            return UriHelper::addPath($url, $path);
        }

        return $url;
    }

    /**
     * Flush all cookies
     */
    public function flushCookies ()
    {
        $this->cookies = array();

        return $this;
    }

    /**
     * Add a Cookie
     *
     * @param string $key
     * @param string $value
     *
     * @return \DBookSecurityClient\Api\Client
     */
    public function addCookie ($key, $value)
    {
        $this->cookies[$key] = $value;

        return $this;
    }

    /**
     * Return the cookies' array as header
     *
     * @return string | boolean
     */
    protected function getCookiesAsHeader()
    {
        return http_build_query($this->cookies, null, ';');
    }

    /**
     * CALL the API, get the status and body
     *
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return Response
     * @throws ApiException
     */
    public function execute($method = self::METHOD_GET, $path, $data = array(), $headers = array())
    {
        $headers = array_merge(array(
            'Accept: application/json'
        ), $headers);

        $url = $this->getUrl($path);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method !== self::METHOD_GET) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            $headers[] = "Content-Type:  application/x-www-form-urlencoded";
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if (false !== ($cookies = $this->getCookiesAsHeader())) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookies);
        }

        $content        = curl_exec($curl);
        $status         = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType    = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $errorNumber    = curl_errno($curl);
        $error          = curl_error($curl);
        curl_close($curl);

        if ($errorNumber > 0) {
            throw new ApiException(sprintf('SSO failure: HTTP request to server failed. %s', $error));
        }

        switch (Response::assertContentType($contentType)) {
            case Response::TYPE_JSON:
                return new JsonResponse($status, $contentType, $content, $url);
            default:
                return new Response($status, $contentType, $content, $url);
        }
    }

    /**
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return Response
     * @throws ApiException
     */
    public function apiGet($path, $data = array(), $headers = array())
    {
        return $this->execute(self::METHOD_GET, $path, $data, $headers);
    }

    /**
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return Response
     * @throws ApiException
     */
    public function apiPost($path, $data = array(), $headers = array())
    {
        return $this->execute(self::METHOD_POST, $path, $data, $headers);
    }

    /**
     * @return Response
     */
    public function logout ()
    {
        return $this->apiPost('/logout');
    }

    /**
     * @return array
     */
    public function completeLogout ()
    {
        return $this->logout();
    }

    /**
     * Try to get a token per product
     *
     * @param array $products
     *
     * @return string | boolean
     */
    public function takeToken ($products)
    {
        return false;
    }

    /**
     * Free products tokens
     *
     * @param array  $products
     *
     * @return string | boolean
     */
    public function freeToken ($products)
    {
        return false;
    }

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string  $token
     *
     * @return User|boolean
     */
    public function getUserByOauth2Token($token)
    {
        $response = $this->apiGet(self::METHOD_GET, '/users/oauth2/' . $token);
        if ($response->getStatus() === 200) {
            return new User($response->getContent());
        }

        return null;
    }

    /**
     * @param string $filter
     * @return array
     */
    public function getSchools($filter = null)
    {
        return $this->apiPost('/crm/schools', array('search' => $filter))->getContent();
    }

    /**
     * @param string $id
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param array $schools
     * @return array
     */
    public function profile($id, $firstName, $lastName, $role, $schools)
    {
        return $this->apiGet('/profile', array(
            'user_id' => $id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'schools' => $schools,
            'role' => $role,
        ))->getContent();
    }
}