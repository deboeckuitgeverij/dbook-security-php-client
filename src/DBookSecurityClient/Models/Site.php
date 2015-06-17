<?php
namespace DBookSecurityClient\Models;

use DBookSecurity\Constants AS DBCST;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Site
{
    
    /**
     * Site name
     * @var string
     */
    protected $name = null;

    /**
     * Url
     * @var string
     */
    protected $url = null;

    /**
     * Image
     * @var string
     */
    protected $image = null;

    /**
     * Constructor
     * 
     * @param mixed $p_record
     */
    public function __construct ($p_record = array())
    {
        
    }

    /**
     * Set site name
     * 
     * @param string $p_name
     * 
     * @return \DBookSecurityClient\Models\Site
     */
    public function setName ($p_name)
    {
        $this->name = $p_name;
        
        return $this;
    }

    /**
     * Get site name
     * 
     * @return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * Set url
     * 
     * @param string $p_url
     * 
     * @return \DBookSecurityClient\Models\Site
     */
    public function setUrl ($p_url)
    {
        $this->url = $p_url;
        
        return $this;
    }

    /**
     * Get url
     * 
     * @return string
     */
    public function getUrl ()
    {
        return $this->url;
    }

    /**
     * Set image
     * 
     * @param string $p_image
     * 
     * @return \DBookSecurityClient\Models\Site
     */
    public function setImage ($p_image)
    {
        $this->image = $p_image;
        
        return $this;
    }

    /**
     * Get image
     * 
     * @return string
     */
    public function getImage ()
    {
        return $this->image;
    }

    /**
     * Valid ??
     *
     * @return boolean
     */
    public function isValid ()
    {
        if ($this->getName() != '') {
            
            return true;
        }
    
        return false;
    }

}