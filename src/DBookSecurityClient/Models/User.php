<?php
namespace DBookSecurityClient\Models;

use DBookSecurityClient\Constants AS DBCST;
use DBookSecurityClient\Models\Product;
use DBookSecurityClient\Models\Site;
use DBookSecurityClient\Services\Tools;

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
     * User role
     * @var string
     */
    protected $role = null;

    /**
     * User preferred language
     * @var string
     */
    protected $preferred_language = DBCST::LANGUAGE_UNKNOWN;

    /**
     * User products
     * @var array
     */
    protected $products = array();

    /**
     * User sites
     * @var array
     */
    protected $sites = array();

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
            if (array_key_exists('login', $p_datas)) {
                $this->setLogin($p_datas['login']);
            }
            if (array_key_exists('role', $p_datas)) {
                $this->setRole($p_datas['role']);
            }
            if (array_key_exists('first_name', $p_datas)) {
                $this->setFirstname($p_datas['first_name']);
            }
            if (array_key_exists('last_name', $p_datas)) {
                $this->setLastname($p_datas['last_name']);
            }
            if (array_key_exists('lang', $p_datas)) {
                $this->setPreferredLanguage($p_datas['lang']);
            }
            if (array_key_exists('products', $p_datas)) {
                $this->addProducts(Tools::obj2Array($p_datas['products']));
            }
            if (array_key_exists('sites', $p_datas)) {
                $this->addSites(Tools::obj2Array($p_datas['sites']));
            }
        }
    }

    /**
     * Id setter
     * 
     * @param string $p_id
     * 
     * @return \DBookSecurityClient\Models\User
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
     * @return \DBookSecurityClient\Models\User
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
     * @return \DBookSecurityClient\Models\User
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
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $p_role
     */
    public function setRole($p_role)
    {
        $this->role = $p_role;
    }

    /**
     * Firstname setter
     * 
     * @param string $p_firstname
     * 
     * @return \DBookSecurityClient\Models\User
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
     * @return \DBookSecurityClient\Models\User
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
     * Flush products
     * 
     * @return \DBookSecurityClient\Models\User
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
     * @param \DBookSecurityClient\Models\Product $p_product
     * 
     * @return \DBookSecurityClient\Models\User
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
     * @return \DBookSecurityClient\Models\Product |boolean
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
     * Get all sites
     * 
     * @return array
     */
    public function getSites ()
    {
        return $this->sites;
    }

    /**
     * Add a site
     * 
     * @param \DBookSecurityClient\Models\Site $p_site
     * 
     * @return \DBookSecurityClient\Models\User
     */
    public function addSite ($p_site)
    {
        $found = false;
        foreach ($this->sites as $oneSite) {
            if ($oneSite->getName() == $p_site->getName()) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->sites[] = $p_site;
        }
        
        return $this;
    }

    /**
     * Set preferred language
     * 
     * @param string $p_lang
     * 
     * @return \DBookSecurityClient\Models\User
     */
    public function setPreferredLanguage ($p_lang)
    {
        $this->preferred_language = DBCST::LANGUAGE_UNKNOWN;
        if (in_array($p_lang, DBCST::getLanguages())) {
            $this->preferred_language = $p_lang;
        }
        
        return $this;
    }

    /**
     * Return preferred language
     * 
     * @return string
     */
    public function getPreferredLanguage ()
    {
        return $this->preferred_language;
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

    /**
     * Add products
     * 
     * @param mixed $p_datas
     * 
     * @return \DBookSecurityClient\Models\User
     */
    protected function addProducts ($p_datas)
    {
        if (is_array($p_datas)) {
            foreach ($p_datas as $key=>$oneProduct) {
                $product = new Product($oneProduct);
                if ($product->isValid()) {
                    $this->addProduct($product);
                }
            }
        }
        
        return $this;
    }

    /**
     * Add sites
     *
     * @param mixed $p_datas
     *
     * @return \DBookSecurityClient\Models\User
     */
    protected function addSites ($p_datas)
    {
        if (is_array($p_datas)) {
            foreach ($p_datas as $key=>$oneSite) {
                $site = new Site($oneSite);
                if ($site->isValid()) {
                    $this->addSite($site);
                }
            }
        }
    
        return $this;
    }

}