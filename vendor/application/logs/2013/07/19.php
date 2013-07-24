<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-07-19 01:54:44 --- EMERGENCY: Kohana_Exception [ 0 ]: The extra_password property does not exist in the Model_Account class ~ MODPATH/orm/classes/Kohana/ORM.php [ 684 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php:600
2013-07-19 01:54:44 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(600): Kohana_ORM->get('extra_password')
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(124): Kohana_ORM->__get('extra_password')
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php:600