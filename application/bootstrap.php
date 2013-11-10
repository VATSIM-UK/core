<?php

defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH . 'classes/Kohana/Core' . EXT;

if (is_file(APPPATH . 'classes/Kohana' . EXT)) {
    // Application extends the core
    require APPPATH . 'classes/Kohana' . EXT;
} else {
    // Load empty core extension
    require SYSPATH . 'classes/Kohana' . EXT;
}

/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('Europe/London');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Kohana', 'auto_load_lowercase'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV'])) {
    Kohana::$environment = constant('Kohana::' . strtoupper($_SERVER['KOHANA_ENV']));
}

Kohana::$environment = isset($_SERVER['KOHANA_ENV']) ? constant('Kohana::' . strtoupper($_SERVER['KOHANA_ENV'])) : Kohana::PRODUCTION;

if(isset($_SERVER["PWD"])){
    $dev = preg_match("/(httpdocs|dev)/i", $_SERVER["PWD"]);
} else {
    $dev = false;
}
if(Kohana::$environment == Kohana::PRODUCTION && $dev){
    Kohana::$environment = Kohana::DEVELOPMENT;
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
// INIT!
$_SERVER_URI = explode("/", $_SERVER["SCRIPT_NAME"]);
array_pop($_SERVER_URI);
$_SERVER_URI = str_replace("//", "/", implode("/", $_SERVER_URI)."/");
Kohana::init(array(
    'base_url' => ((Kohana::$environment==Kohana::DEVELOPMENT) ? "http://dev.vatsim-uk.co.uk".$_SERVER_URI : "http://core.vatsim-uk.co.uk".$_SERVER_URI),
    'index_file' => "",//Kohana::$environment === Kohana::PRODUCTION,
    'errors' => Kohana::$environment !== Kohana::PRODUCTION,
    'profile' => Kohana::$environment !== Kohana::PRODUCTION,
    'caching' => Kohana::$environment === Kohana::PRODUCTION,
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH . 'logs'), array(Log::ALERT, Log::CRITICAL, Log::EMERGENCY, Log::ERROR, Log::WARNING));
Kohana::$log->attach(new Log_Database());

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
    //auth' => MODPATH . 'auth', // Basic authentication
    'cache'      => MODPATH.'cache',      // Caching with multiple backends
    'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    'database' => MODPATH . 'database', // Database access
    'image' => MODPATH . 'image', // Image manipulation
    'minion'     => MODPATH.'minion',     // CLI Tasks
    'orm' => MODPATH . 'orm', // Object Relationship Mapping
    'vatsim' => MODPATH . 'vatsim', // Vatsim interface scripts
    'email' => MODPATH . 'email', // Shadowhand emailer.
    //'kostache' => MODPATH . 'kostache', // Templating system (Kohana version of Mustache)
    //'kophery' => MODPATH . 'kophery', // Kohana version of Phery (JS AJAX LIBRARY)
        // 'unittest'   => MODPATH.'unittest',   // Unit testing
        // 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));

/** CUSTOM SETTINGS **/
//1 - If the system user doesn't exist, create them.
$_sysUsr = ORM::factory("Account_Main", Kohana::$config->load("general")->get("system_user"));
if(!$_sysUsr->loaded()){
    $_sysUsr = ORM::factory("Account");
    $_sysUsr->id = Kohana::$config->load("general")->get("system_user");
    $_sysUsr->name_first = "VATSIM";
    $_sysUsr->name_last = "UK";
    $_sysUsr->status = 7;
    $_sysUsr->password = "somewhere_over_the_rainbow25js1";
    $_sysUsr->created = gmdate("Y-m-d H:i:s");
    $_sysUsr->save();
}
if(count($_sysUsr->emails->find_all()) < 1){
    $email = ORM::factory("Account_Email");
    $email->account_id = $_sysUsr->id;
    $email->email = "outbound@vatsim-uk.co.uk";
    $email->primary = 1;
    $email->created = gmdate("Y-m-d H:i:s");
    $email->save();
}

/**
 * Include separate routes file
 */
require_once APPPATH.'config/routes.php';