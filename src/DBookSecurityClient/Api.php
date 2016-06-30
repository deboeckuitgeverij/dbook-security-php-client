<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Api\JsonResponse;
use DBookSecurityClient\Api\Response;
use DBookSecurityClient\Gate\Gate;
use DBookSecurityClient\Model\User;
use DBookSecurityClient\Api\Client;

class Api
{
    const CACHE_USER = 'user';

    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param Gate $gate
     */
    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    /**
     * @return Gate
     */
    public function getGate()
    {
        return $this->gate;
    }

    public function cache($key, $value)
    {
        $this->cache[$key] = $value;
    }

    public function getCached($key, $default = null)
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        return $default;
    }

    public function gateGet($path, $data = array(), $headers = array())
    {
        return $this->gate->call(Client::METHOD_GET, $path, $data, $headers);
    }

    public function gatePost($path, $data = array(), $headers = array())
    {
        return $this->gate->call(Client::METHOD_POST, $path, $data, $headers);
    }

    /**
     * @param Response $response
     * @param null $errorMessage
     * @return User
     * @throws ApiException
     */
    protected function assertUserResponse(Response $response, $errorMessage = null)
    {
        if ($response->isSuccess() && $response instanceof JsonResponse) {
            $this->cache(self::CACHE_USER, new User($response->getContent()));
        } else {
            throw new ApiException($errorMessage ?: 'API error', $response);
        }

        return $this->getCached(self::CACHE_USER);
    }

    public function getHelp()
    {
        return $this->gateGet('/help')->getContent();
    }

    public function getInfo()
    {
        return $this->gateGet('/info')->getContent();
    }

    public function getCDSSO()
    {
        $response = $this->gateGet('/cdsso');

        if (!$response->isSuccess()) {
            throw new ApiException('CDSSO call failed', $response);
        }

        return $response->getContent();
    }

    /**
     * @return User|null
     * @throws ApiException
     */
    public function getUser()
    {
        if (!$this->getCached(self::CACHE_USER) instanceof User) {
            return $this->assertUserResponse(
                $this->gatePost('/user'),
                'Failed to fetch logged in user'
            );
        }

        return $this->getCached(self::CACHE_USER);
    }

    /**
     * Try to get a user with an OAuth 2.0 token
     *
     * @param string  $token
     * @return User|null
     */
    public function getUserWithToken($token)
    {
        return $this->assertUserResponse(
            $this->gateGet('/users/oauth2/' . $token),
            'Failed to fetch OAuth user with token'
        );
    }

    /**
     * @return bool
     */
    public function isAuthenticated()
    {
        try {
            return $this->getUser() instanceof User;
        } catch (ApiException $e) {}

        return false;
    }

    /**
     * perform authentication through the configured Gate
     *
     * @param array $options
     * @return User
     * @throws ApiException
     */
    public function authenticate(array $options = array())
    {
        return $this->assertUserResponse(
            $this->gate->authenticate($options),
            'authentication failed'
        );
    }

    /**
     * @return Response
     */
    public function logout()
    {
        return $this->gate->call(Client::METHOD_POST, '/logout');
    }

    /**
     * @param string $login
     * @param string $password
     * @param string $confirm
     * @param string $role
     * @param string|null $firstname
     * @param string|null $lastname
     * @param array|null $schools
     * @return User
     */
    public function register($login, $password, $confirm, $role, $firstname = null, $lastname = null, $schools = null)
    {
        $data = array(
            'login' => $login,
            'password' => $password,
            'confirm' => $confirm,
            'role' => $role,
            'first_name' => $firstname,
            'last_name' => $lastname,
        );

        if (is_array($schools)) {
            $data['schools'] = $schools;
        }

        return $this->assertUserResponse(
            $this->gatePost('/register', $data),
            'user registration failed'
        );
    }

    public function updateUser($id, $role, $firstname, $lastname, $schools = null)
    {
        $data = array(
            'user_id' => $id,
            'role' => $role,
            'first_name' => $firstname,
            'last_name' => $lastname,
        );

        if (is_array($schools)) {
            $data['schools'] = $schools;
        }

        return $this->assertUserResponse(
            $this->gatePost('/profile', $data),
            'user profile update failed'
        );
    }

    public function updateUserPassword($id, $old, $new)
    {
        $data = array(
            'user_id' => $id,
            'old' => $old,
            'password' => $new,
        );

        return $this->assertUserResponse(
            $this->gatePost('/passworduser', $data),
            'user password update failed'
        );
    }

    public function getSchools($search = null)
    {
        $response = $this->gatePost('/crm/schools', array(
            'search' => $search
        ));

        if ($response->isSuccess()) {
            return $response->getContent();
        }

        throw new ApiException('Failed to fetch schools', $response);
    }

    public function activateProductCode($code)
    {

    }
}