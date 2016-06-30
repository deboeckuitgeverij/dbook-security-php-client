<?php
namespace DBookSecurityClient\Model;

class Product
{
    const AUDIENCE_ALL     = 'ALL';
    const AUDIENCE_TEACHER = 'TEACHER';
    const AUDIENCE_STUDENT = 'STUDENT';

    const PRODUCT_MODE_GROUP = 'GROUP';
    const PRODUCT_MODE_USER  = 'USER';

    const PRODUCT_TYPE_STANDARD = 'STANDARD';
    const PRODUCT_TYPE_ABO      = 'ABO';
    const PRODUCT_TYPE_DEMO     = 'DEMO';

    const PRODUCT_DURATION_UNLIMITED = 'UNLIMITED';
    const PRODUCT_DURATION_1M        = '1M';
    const PRODUCT_DURATION_1Y        = '1Y';  // 1 year : 01/01 to 31/12
    const PRODUCT_DURATION_1SY       = '1SY'; // 1 school year

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
     * @var \DateTime
     */
    protected $from = null;

    /**
     * Product valid to
     * @var \DateTime
     */
    protected $to = null;

    /**
     * Product mode
     * @var string
     */
    protected $mode = self::PRODUCT_MODE_USER;

    /**
     * Product type
     * @var string
     */
    protected $type = self::PRODUCT_TYPE_STANDARD;

    /**
     * Product duration
     * @var string
     */
    protected $duration = self::PRODUCT_DURATION_UNLIMITED;

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
     * Product audience
     * @var string
     */
    protected $audience = DBCST::AUDIENCE_ALL;

    /**
     * Constructor
     * 
     * @param mixed $p_record
     */
    public function __construct ($p_record = array())
    {
        if (is_array($p_record) && count($p_record) > 0) {
            if (array_key_exists('id', $p_record)) {
                $this->setId($p_record['id']);
            }
            if (array_key_exists('code', $p_record)) {
                $this->setCode($p_record['code']);
            }
            if (array_key_exists('name', $p_record)) {
                $this->setName($p_record['name']);
            }
            if (array_key_exists('from', $p_record)) {
                $this->setFrom($p_record['from']);
            }
            if (array_key_exists('to', $p_record)) {
                $this->setTo($p_record['to']);
            }
            if (array_key_exists('mode', $p_record)) {
                $this->setMode($p_record['mode']);
            }
            if (array_key_exists('type', $p_record)) {
                $this->setType($p_record['type']);
            }
            if (array_key_exists('group', $p_record)) {
                $this->setGroup($p_record['group']);
            }
            if (array_key_exists('duration', $p_record)) {
                $this->setDuration($p_record['duration']);
            }
            if (array_key_exists('audience', $p_record)) {
                $this->setAudience($p_record['audience']);
            }
        }
    }

    /**
     * Id setter
     * 
     * @param string $p_id
     * 
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * 
     * @return $this
     */
    public function setType ($p_type)
    {
        $this->type  = DBCST::PRODUCT_TYPE_STANDARD;
        if (in_array($p_type, DBCST::getProductTypes())) {
            $this->type = $p_type;
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
     * Set mode
     * 
     * @param string $p_mode
     * 
     * @return $this
     */
    public function setMode ($p_mode)
    {
        $this->mode = $p_mode;
        
        return $this;
    }

    /**
     * Get mode
     * 
     * @return string
     */
    public function getMode ()
    {
        return $this->mode;
    }

    /**
     * Set group
     * 
     * @param string $p_group
     * 
     * @return $this
     */
    public function setGroup ($p_group)
    {
        $this->group = $p_group;
        
        return $this;
    }

    /**
     * Get group
     * 
     * @return string
     */
    public function getGroup ()
    {
        return $this->group;
    }

    /**
     * Set duration
     * 
     * @param string $p_duration
     * 
     * @return $this
     */
    public function setDuration ($p_duration)
    {
        $this->duration = DBCST::PRODUCT_DURATION_UNLIMITED;
        if (in_array($p_duration, DBCST::getProductDurations())) {
            $this->duration = $p_duration;
        }
        
        return $this;
    }

    /**
     * Get duration
     * 
     * @return string
     */
    public function getDuration ()
    {
        return $this->duration;
    }

    /**
     * Tokens setter
     * 
     * @param number $p_tokens
     * 
     * @return $this
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

    /**
     * Set audience
     * 
     * @param string $p_audience
     * 
     * @return $this
     */
    public function setAudience ($p_audience)
    {
        $this->audience = DBCST::AUDIENCE_ALL;
        if (in_array($p_audience, DBCST::getAudiences())) {
            $this->audience = $p_audience;
        }
        
        return $this;
    }

    /**
     * Get audience
     * 
     * @return string
     */
    public function getAudience ()
    {
        return $this->audience;
    }

    /**
     * Valid ??
     * 
     * @return boolean
     */
    public function isValid ()
    {
        if ($this->getCode() != '') {
            
            return true;
        }
        
        return false;
    }

}