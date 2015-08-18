<?php

namespace Core\GoogleBundle\Entity;

class GoogleHelper
{
    public static $data =
array(
    "sk" => array(
    ),
);
    public static function getCategories($locale)
    {
        $key = (array_key_exists($locale, self::$data)) ? $locale : "sk";
        
        return (array_key_exists($key, self::$data)) ? self::$data[$key] : array();
    }
    
}
