
<?php

defined('SYSPATH') OR die('No direct script access.');

class Cookie extends Kohana_Cookie {
    
    // Set a new salt
    public static $salt = "eWw96yc2KLLzjr1CzYAVF5ePbXcHZVZLvtUJSh2CtVmBTeHvg83NnfzE1bYpL1Jxrg3qRt1mdoPm0xJS";
 
    // Don't allow javascript access to cookies
    public static $httponly = TRUE;

    /**
     * @var  mixed  default encryption instance
     */
    public static $encryption = 'tripledes';

    /**
     * Sets an encrypted cookie.
     *
     * @uses  Cookie::set
     * @uses  Encrypt::encode
     */
    public static function encrypt($name, $value, $expiration = NULL) {
        $value = Encrypt::instance(Cookie::$encryption)->encode((string) $value);

        parent::set($name, $value, $expiration);
    }

    /**
     * Gets an encrypted cookie.
     *
     * @uses  Cookie::get
     * @uses  Encrypt::decode
     */
    public static function decrypt($name, $default = NULL) {
        if ($value = parent::get($name, NULL)) {
            $value = Encrypt::instance(Cookie::$encryption)->decode($value);
        }

        return isset($value) ? $value : $default;
    }

}