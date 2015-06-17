<?php
namespace DBookSecurityClient\Services;

/**
 * 
 * @author jklam
 *
 */
class Tools
{

    /**
     * 
     * @param mixed $p_object
     * 
     * @return array
     */
    public static function obj2Array ($p_object)
    {
        if (is_object($p_object)) {
            $clone = get_object_vars($p_object);
        } else {
            if (is_array($p_object)) {
                $clone = $p_object;
            } else {
                
                return $p_object;
            }
        }
        foreach ($clone as $key=>$value) {
            $clone[$key] = self::obj2Array($value);
        }
        
        return $clone;
    }

}