<?php
namespace DBookSecurity\Client\Model;

use DBookSecurity\Constants AS DBCST;
use DBookSecurity\Client\Model\Product;

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
     * User products
     * @var array
     */
    protected $products = array();

    /**
     * Id setter
     * 
     * @param string $p_id
     * 
     * @return \DBookSecurity\Client\Model\User
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
     * Title setter
     * 
     * @param string $p_title
     * 
     * @return \DBookSecurity\Client\Model\User
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
     * @return \DBookSecurity\Client\Model\User
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
     * @return \DBookSecurity\Client\Model\User
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
     * Flush products
     * 
     * @return \DBookSecurity\Client\Model\User
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
     * @return \DBookSecurity\Client\Model\User
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

}