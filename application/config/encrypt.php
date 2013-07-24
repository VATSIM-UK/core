
<?php defined('SYSPATH') or die('No direct script access.');
// application/config/encrypt.php
 
return array(
    'default' => array(
        'key'    => 'c72rVXPZEUFD8gcJs2FoBUW27MO1RNVx1ymdekHu2US4sFxCFBgM6sq2lmMqfw8',
        'cipher' => MCRYPT_RIJNDAEL_128,
        'mode'   => MCRYPT_MODE_NOFB,
    ),
    'blowfish' => array(
        'key'    => 'UoGPGDJnxB4smES5Hqr7qq80x6DSgIMvZfz2cNU2vrMEWPFaKhRExtS6rY7aWpQ',
        'cipher' => MCRYPT_BLOWFISH,
        'mode'   => MCRYPT_MODE_ECB,
    ),
    'tripledes' => array(
        'key'    => 'VlT65cOSXDlI1k2sAO1LEgIsEMtzcOL5VNI3UgF623nXNJk6ZhnpIprsHHFu9gE',
        'cipher' => MCRYPT_3DES,
        'mode'   => MCRYPT_MODE_CBC,
    ),
);