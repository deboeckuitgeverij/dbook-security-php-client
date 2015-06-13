<?php
namespace DBookSecurityClient\Models;

use DBookSecurityConstants AS DBCST;

/**
 *
 * @author jérôme klam <jerome.klam@deboeck.com>
 *
 */
class Product
{

    /**
     * Types constants
     * @var string
     */
    const TYPE_USER  = 'user';
    const TYPE_GROUP = 'group';

    /**
     * Product id
     * @var string
     */
    protected $id = null;

    /**
     * Product code
     * @var string
     */
    protected $code = null;

    /**
     * Product name
     * @var string
     */
    protected $name = null;

    /**
     * Product valid from
     * @var datetime
     */
    protected $from = null;

    /**
     * Product valid to
     * @var datetime
     */
    protected $to = null;

    /**
     * Product type
     * @var string
     */
    protected $type = null;

    /**
     * Group name
     * @var string
     */
    protected $group = null;

    /**
     * Product tokens
     * @var number
     */
    protected $tokens = null;

    /**
     * Id setter
     * 
     * @param string $p_id
     * 
     * @return \DBookSecurityClient\Models\Product
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
     * Code setter
     * 
     * @param string $p_code
     * 
     * @return \DBookSecurityClient\Models\Product
     */
    public function setCode ($p_code)
    {
        $this->code = $p_code;
        return $this;
    }

    /**
     * Code getter
     * 
     * @return string
     */
    public function getCode ()
    {
        return $this->code;
    }

    /**
     * Name setter
     * 
     * @param string $p_name
     * 
     * @return \DBookSecurityClient\Models\Product
     */
    public function setName ($p_name)
    {
        $this->name = $p_name;
        return $this;
    }

    /**
     * Name getter
     * 
     * }return string
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * From setter
     * 
     * @param datetime $p_from
     * 
     * @return \DBookSecurityClient\Models\Product
     */
    public function setFrom ($p_from)
    {
        $this->from = $p_from;
        return $this;
    }

    /**
     * From getter
     * 
     * @return datetime
     */
    public function getFrom ()
    {
        return $this->from;
    }

    /**
     * To setter
     * 
     * @param datetime $p_to
     * 
     * @return \DBookSecurityClient\Models\Product
     */
    public function setTo ($p_to)
    {
        $this->to = $p_to;
        return $this;
    }

    /**
     * To getter
     * 
     * @return datetime
     */
    public function getTo ()
    {
        return $this->to;
    }

    /**
     * Type setter
     * 
     * @param string $p_type
     * @param string $p_group
     * 
     * @return \DBookSecurityClient\Models\Product
     */
    public function setType ($p_type, $p_group = null)
    {
        $this->type  = self::TYPE_USER;
        $this->group = null;
        if (in_array($p_type, array(self::TYPE_USER, self::TYPE_GROUP))) {
            $this->type = $p_type;
            if ($p_type === self::TYPE_GROUP) {
                $this->group = $p_group;
            }
        }
        return $this;
    }

    /**
     * Type getter
     * 
     * @return string
     */
    public function getType ()
    {
        return $this->type;
    }

    /**
     * Tokens setter
     * 
     * @param number $p_tokens
     * 
     * @return \DBookSecurityClient\Models\Product
     */
    public function setTokens ($p_tokens)
    {
        $this->tokens = $p_tokens;
        return $this;
    }

    /**
     * Tokens getter
     * 
     * @return number
     */
    public function getTokens ()
    {
        return $this->tokens;
    }

}