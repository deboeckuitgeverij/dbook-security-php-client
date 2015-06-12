<?php
namespace DBookSecurityClient\Model;

use DBookSecurity\Constants AS DBCST;
use DBookSecurityClient\Model\Product;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class User
{

    /**
     * User id
     * @var string
     */
    protected $id = null;

    /**
     * User login
     * @var string
     */
    protected $login = null;

    /**
     * User password (not crypt)
     * @var string
     */
    protected $password = null;

    /**
     * User title
     * @var string
     */
    protected $title = null;

    /**
     * User firstname
     * @var string
     */
    protected $firstname = null;

    /**
     * User lastname
     * @var string
     */
    protected $lastname = null;

    /**
     * User email
     * @var string
     */
    protected $email = null;

    /**
     * User products
     * @var array
     */
    protected $products = array();

    /**
     * Constructor
     * 
     * @param array $p_datas
     */
    public function __construct ($p_datas = array())
    {
        if (is_array($p_datas) && count($p_datas) > 0) {
            if (array_key_exists('id', $p_datas)) {
                $this->setId($p_datas['id']);
            }
            if (array_key_exists('title', $p_datas)) {
                $this->setTitle($p_datas['title']);
            }
            if (array_key_exists('first_name', $p_datas)) {
                $this->setFirstname($p_datas['first_name']);
            }
            if (array_key_exists('last_name', $p_datas)) {
                $this->setLastname($p_datas['last_name']);
            }
        }
    }

    /**
     * Id setter
     * 
     * @param string $p_id
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setId ($p_id)
    {
        $this->id = $p_id;
        return $this;
    }

    /**
     * Id getter
     * 
     * @return string
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Login setter
     * 
     * @param string $p_login
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setLogin ($p_login)
    {
        $this->login = $p_login;
        return $this;
    }

    /**
     * Login getter
     * 
     * @return string
     */
    public function getLogin ()
    {
        return $this->login;
    }

    /**
     * Password setter
     * 
     * @param string $p_paswd
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setPassword ($p_paswd)
    {
        $this->password = $p_paswd;
        return $this;
    }

    /**
     * Password getter
     * 
     * @return string
     */
    public function getPassword ()
    {
        return $this->password;
    }

    /**
     * Title setter
     * 
     * @param string $p_title
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setTitle ($p_title)
    {
        $this->title = $p_title;
        return $this;
    }

    /**
     * Title getter
     * 
     * @return string
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     * Firstname setter
     * 
     * @param string $p_firstname
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setFirstname ($p_firstname)
    {
        $this->firstname = $p_firstname;
        return $this;
    }

    /**
     * Firstname getter
     * 
     * @return string
     */
    public function getFirstname ()
    {
        return $this->firstname;
    }

    /**
     * Lastname setter
     * 
     * @param string $p_lastname
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setLastname ($p_lastname)
    {
        $this->lastname = $p_lastname;
        return $this;
    }

    /**
     * Lastname getter
     * 
     * @return string
     */
    public function getLastname ()
    {
        return $this->lastname;
    }

    /**
     * Email setter
     * 
     * @param string $p_email
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function setEmail ($p_email)
    {
        $this->email = $p_email;
        return $this;
    }

    /**
     * Email getter
     * 
     * @return string
     */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
     * Flush products
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function flushProducts ()
    {
        $this->products = array();
        return $this;
    }

    /**
     * Products getter
     * 
     * @return array
     */
    public function getProducts ()
    {
        return $this->products;
    }

    /**
     * Add one product
     * 
     * @param Product $p_product
     * 
     * @return \DBookSecurityClient\Model\User
     */
    public function addProduct (Product $p_product)
    {
        $found = false;
        foreach ($this->products as $oneProduct) {
            if ($oneProduct->getId() == $p_product->getId()) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->products[] = $p_product;
        }
        return $this;
    }

    /**
     * Get Product by code
     * 
     * @param string $p_code
     * 
     * @return Product|boolean
     */
    public function getProductByCode ($p_code)
    {
        foreach ($this->products as $oneProduct) {
            if ($oneProduct->getCode() == $p_code) {
                return $oneProduct;
            }
        }
        return false;
    }

    /**
     * Validate object
     * 
     * @return boolean
     */
    public function isValid ()
    {
        if ($this->getId() === null || $this->getLogin() === null || $this->getEmail() === null) {
            return false;
        }
        return true;
    }

}