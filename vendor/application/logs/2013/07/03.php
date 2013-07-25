<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-07-03 14:27:05 --- EMERGENCY: ErrorException [ 4096 ]: Method Model_Account_Email::__toString() must return a string value ~ SYSPATH/classes/Kohana/Cookie.php [ 158 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php:158
2013-07-03 14:27:05 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php(158): Kohana_Core::error_handler(4096, 'Method Model_Ac...', '/var/www/dev.va...', 158, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php(115): Kohana_Cookie::salt('vuk_sso_email', Object(Model_Account_Email))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(102): Kohana_Cookie::set('vuk_sso_email', Object(Model_Account_Email), 60)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php:158
2013-07-03 14:41:33 --- EMERGENCY: ErrorException [ 1 ]: Undefined class constant 'ACTION_EMAIL_CREATE_USER_PRIMARY' ~ APPPATH/classes/Controller/Account/Manage.php [ 37 ] in :
2013-07-03 14:41:33 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :