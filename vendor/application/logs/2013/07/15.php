<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-07-15 18:21:09 --- EMERGENCY: ErrorException [ 8 ]: Undefined property: Controller_Account_Session::$account ~ APPPATH/classes/Controller/Account/Session.php [ 144 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php:144
2013-07-15 18:21:09 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(144): Kohana_Core::error_handler(8, 'Undefined prope...', '/var/www/dev.va...', 144, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login_redirect()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php:144
2013-07-15 18:27:11 --- EMERGENCY: ErrorException [ 2 ]: json_decode() expects parameter 1 to be string, object given ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php [ 47 ] in :
2013-07-15 18:27:11 --- DEBUG: #0 [internal function]: Kohana_Core::error_handler(2, 'json_decode() e...', '/var/www/dev.va...', 47, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php(47): json_decode(Object(stdClass))
#2 {main} in :
2013-07-15 18:27:33 --- EMERGENCY: ErrorException [ 8 ]: Trying to get property of non-object ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php [ 49 ] in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php:49
2013-07-15 18:27:33 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php(49): Kohana_Core::error_handler(8, 'Trying to get p...', '/var/www/dev.va...', 49, Array)
#1 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php:49
2013-07-15 18:37:09 --- EMERGENCY: Kohana_Exception [ 0 ]: The email property does not exist in the Model_Account class ~ MODPATH/orm/classes/Kohana/ORM.php [ 757 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php:699
2013-07-15 18:37:09 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(699): Kohana_ORM->set('email', Object(Model_Account_Email))
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(145): Kohana_ORM->__set('email', Object(Model_Account_Email))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login_redirect()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php:699
2013-07-15 18:39:09 --- EMERGENCY: ErrorException [ 2 ]: mcrypt_encrypt() expects parameter 3 to be string, array given ~ SYSPATH/classes/Kohana/Encrypt.php [ 171 ] in :
2013-07-15 18:39:09 --- DEBUG: #0 [internal function]: Kohana_Core::error_handler(2, 'mcrypt_encrypt(...', '/var/www/dev.va...', 171, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Encrypt.php(171): mcrypt_encrypt('tripledes', 'VlT65cOSXDlI1k2...', Array, 'cbc', '???4?>8,')
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(150): Kohana_Encrypt->encode(Array)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login_redirect()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#9 {main} in :
2013-07-15 18:39:20 --- EMERGENCY: ErrorException [ 2 ]: mcrypt_encrypt() expects parameter 3 to be string, array given ~ SYSPATH/classes/Kohana/Encrypt.php [ 171 ] in :
2013-07-15 18:39:20 --- DEBUG: #0 [internal function]: Kohana_Core::error_handler(2, 'mcrypt_encrypt(...', '/var/www/dev.va...', 171, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Encrypt.php(171): mcrypt_encrypt('tripledes', 'VlT65cOSXDlI1k2...', Array, 'cbc', '??8?6?J?')
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(150): Kohana_Encrypt->encode(Array)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login_redirect()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#9 {main} in :
2013-07-15 18:39:38 --- EMERGENCY: ErrorException [ 2 ]: Insufficient data for unserializing - 561 required, 559 present ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php [ 44 ] in :
2013-07-15 18:39:38 --- DEBUG: #0 [internal function]: Kohana_Core::error_handler(2, 'Insufficient da...', '/var/www/dev.va...', 44, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php(44): unserialize('a:1:{s:5:"email...')
#2 {main} in :
2013-07-15 18:40:43 --- EMERGENCY: ErrorException [ 8 ]: unserialize(): Error at offset 0 of 696 bytes ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php [ 44 ] in :
2013-07-15 18:40:43 --- DEBUG: #0 [internal function]: Kohana_Core::error_handler(8, 'unserialize(): ...', '/var/www/dev.va...', 44, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login_sso.php(44): unserialize('?jJO?>????R?jzs...')
#2 {main} in :
2013-07-15 18:43:07 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: _acc ~ APPPATH/classes/Controller/Account/Session.php [ 148 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php:148
2013-07-15 18:43:07 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(148): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 148, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login_redirect()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php:148
2013-07-15 19:05:13 --- EMERGENCY: ErrorException [ 8 ]: Undefined offset: 3 ~ /var/www/rts.vatsim-uk.co.uk/home/login_sso.php [ 43 ] in /var/www/rts.vatsim-uk.co.uk/home/login_sso.php:43
2013-07-15 19:05:13 --- DEBUG: #0 /var/www/rts.vatsim-uk.co.uk/home/login_sso.php(43): Kohana_Core::error_handler(8, 'Undefined offse...', '/var/www/rts.va...', 43, Array)
#1 {main} in /var/www/rts.vatsim-uk.co.uk/home/login_sso.php:43
2013-07-15 19:06:42 --- EMERGENCY: ErrorException [ 8 ]: Undefined offset: 3 ~ /var/www/rts.vatsim-uk.co.uk/home/login_sso.php [ 43 ] in /var/www/rts.vatsim-uk.co.uk/home/login_sso.php:43
2013-07-15 19:06:42 --- DEBUG: #0 /var/www/rts.vatsim-uk.co.uk/home/login_sso.php(43): Kohana_Core::error_handler(8, 'Undefined offse...', '/var/www/rts.va...', 43, Array)
#1 {main} in /var/www/rts.vatsim-uk.co.uk/home/login_sso.php:43