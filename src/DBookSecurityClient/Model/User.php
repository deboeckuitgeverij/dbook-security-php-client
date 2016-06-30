<?php
namespace DBookSecurityClient\Model;

class User
{
    const ROLE_OTHER       = 'OTHER';
    const ROLE_TEACHER     = 'TEACHER';
    const ROLE_STUDENT     = 'STUDENT';

    const LANGUAGE_UNKNOWN = null;
    const LANGUAGE_FR      = 'FR';
    const LANGUAGE_NL      = 'NL';
    const LANGUAGE_EN      = 'EN';

    /**
     * @var string
     */
    protected $id = null;

    /**
     * login is the email address
     *
     * @var string
     */
    protected $login = null;

    /**
     * @var string
     */
    protected $firstname = null;

    /**
     * @var string
     */
    protected $lastname = null;

    /**
     * @var string
     */
    protected $role = null;

    /**
     * @var string
     */
    protected $preferredLanguage = self::LANGUAGE_UNKNOWN;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (is_array($data) && count($data) > 0) {
            if (array_key_exists('id', $data)) {
                $this->id = $data['id'];
            }
            if (array_key_exists('login', $data)) {
                $this->login = $data['login'];
            }
            if (array_key_exists('role', $data)) {
                $this->role = $data['role'];
            }
            if (array_key_exists('first_name', $data)) {
                $this->firstname = $data['first_name'];
            }
            if (array_key_exists('last_name', $data)) {
                $this->lastname = $data['last_name'];
            }
            if (array_key_exists('lang', $data)) {
                $this->setPreferredLanguage($data['lang']);
            }
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Email getter returns the login
     *
     * @return string
     * @deprecated
     */
    public function getEmail ()
    {
        return $this->login;
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function setPreferredLanguage ($lang)
    {
        $this->preferredLanguage = self::LANGUAGE_UNKNOWN;

        if (in_array($lang, self::getLanguages())) {
            $this->preferredLanguage = $lang;
        }
        
        return $this;
    }

    /**
     * @return string
     */
    public function getPreferredLanguage ()
    {
        return $this->preferredLanguage;
    }

    /**
     * Validate object
     * 
     * @return boolean
     */
    public function isValid ()
    {
        if ($this->getId() === null || $this->getLogin() === null) {
            return false;
        }

        return true;
    }

    /**
     * Get all languages
     *
     * @return array
     */
    public static function getLanguages()
    {
        return array(self::LANGUAGE_EN, self::LANGUAGE_FR, self::LANGUAGE_NL);
    }
}