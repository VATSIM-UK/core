<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-07-21 01:40:33 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:40:33 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:40:37 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:40:37 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:06 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:06 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:10 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:10 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:10 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:10 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:11 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:11 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:17 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:17 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:18 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:18 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:18 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:18 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:22 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method ORM::factor() ~ APPPATH/classes/Controller/Membership/Manage.php [ 33 ] in :
2013-07-21 01:41:22 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 01:41:32 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'firstname' in 'where clause' [ SELECT `mship_account`.`id` AS `id`, `mship_account`.`token` AS `token`, `mship_account`.`token_ip` AS `token_ip`, `mship_account`.`name_first` AS `name_first`, `mship_account`.`name_last` AS `name_last`, `mship_account`.`password` AS `password`, `mship_account`.`extra_password` AS `extra_password`, `mship_account`.`gender` AS `gender`, `mship_account`.`age` AS `age`, `mship_account`.`created` AS `created`, `mship_account`.`updated` AS `updated`, `mship_account`.`checked` AS `checked`, `mship_account`.`status` AS `status` FROM `mship_account` AS `mship_account` WHERE `firstname` LIKE 'Jamie' ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:41:32 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', 'Model_Account', Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1060): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1001): Kohana_ORM->_load_result(true)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(34): Kohana_ORM->find_all()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_ajax_search()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:42:21 --- EMERGENCY: Kohana_Exception [ 0 ]: The cid property does not exist in the Model_Account class ~ MODPATH/orm/classes/Kohana/ORM.php [ 684 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php:600
2013-07-21 01:42:21 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(600): Kohana_ORM->get('cid')
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(39): Kohana_ORM->__get('cid')
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_ajax_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php:600
2013-07-21 01:51:15 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'mship_account_qualification.id' in 'where clause' [ SELECT `mship_account_qualification`.`account_id` AS `account_id`, `mship_account_qualification`.`type` AS `type`, `mship_account_qualification`.`value` AS `value`, `mship_account_qualification`.`created` AS `created` FROM `mship_account_qualification` AS `mship_account_qualification` WHERE `mship_account_qualification`.`id` = '811029' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:15 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(271): Kohana_ORM->find()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(46): Kohana_ORM->__construct('811029')
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(42): Kohana_ORM::factory('Account_Qualifi...', '811029')
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:17 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'mship_account_qualification.id' in 'where clause' [ SELECT `mship_account_qualification`.`account_id` AS `account_id`, `mship_account_qualification`.`type` AS `type`, `mship_account_qualification`.`value` AS `value`, `mship_account_qualification`.`created` AS `created` FROM `mship_account_qualification` AS `mship_account_qualification` WHERE `mship_account_qualification`.`id` = '811029' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:17 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(271): Kohana_ORM->find()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(46): Kohana_ORM->__construct('811029')
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(42): Kohana_ORM::factory('Account_Qualifi...', '811029')
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:17 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'mship_account_qualification.id' in 'where clause' [ SELECT `mship_account_qualification`.`account_id` AS `account_id`, `mship_account_qualification`.`type` AS `type`, `mship_account_qualification`.`value` AS `value`, `mship_account_qualification`.`created` AS `created` FROM `mship_account_qualification` AS `mship_account_qualification` WHERE `mship_account_qualification`.`id` = '811029' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:17 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(271): Kohana_ORM->find()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(46): Kohana_ORM->__construct('811029')
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(42): Kohana_ORM::factory('Account_Qualifi...', '811029')
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:31 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'mship_account_qualification.id' in 'where clause' [ SELECT `mship_account_qualification`.`account_id` AS `account_id`, `mship_account_qualification`.`type` AS `type`, `mship_account_qualification`.`value` AS `value`, `mship_account_qualification`.`created` AS `created` FROM `mship_account_qualification` AS `mship_account_qualification` WHERE `mship_account_qualification`.`id` = '811029' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:51:31 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(271): Kohana_ORM->find()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(46): Kohana_ORM->__construct('811029')
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(42): Kohana_ORM::factory('Account_Qualifi...', '811029')
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_ajax_search()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 01:57:54 --- EMERGENCY: ErrorException [ 8 ]: Object of class Model_Account_Qualification could not be converted to int ~ APPPATH/classes/Enum/Account/Qualification/ATC.php [ 16 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Enum/Account/Qualification/ATC.php:16
2013-07-21 01:57:54 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Enum/Account/Qualification/ATC.php(16): Kohana_Core::error_handler(8, 'Object of class...', '/var/www/dev.va...', 16, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(47): Enum_Account_Qualification_ATC::getDescription(Object(Model_Account_Qualification))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_ajax_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Enum/Account/Qualification/ATC.php:16
2013-07-21 02:00:24 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: match ~ APPPATH/classes/Controller/Membership/Manage.php [ 38 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:38
2013-07-21 02:00:24 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(38): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 38, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_ajax_search()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:38
2013-07-21 15:44:16 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Model_Account_Qualification::order() ~ APPPATH/classes/Controller/Membership/Manage.php [ 51 ] in :
2013-07-21 15:44:16 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 15:44:18 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Model_Account_Qualification::order() ~ APPPATH/classes/Controller/Membership/Manage.php [ 51 ] in :
2013-07-21 15:44:18 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 15:44:41 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Model_Account_Qualification::order() ~ APPPATH/classes/Controller/Membership/Manage.php [ 51 ] in :
2013-07-21 15:44:41 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 16:02:53 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column '1' in 'where clause' [ SELECT `mship_account`.`id` AS `id`, `mship_account`.`token` AS `token`, `mship_account`.`token_ip` AS `token_ip`, `mship_account`.`name_first` AS `name_first`, `mship_account`.`name_last` AS `name_last`, `mship_account`.`password` AS `password`, `mship_account`.`extra_password` AS `extra_password`, `mship_account`.`gender` AS `gender`, `mship_account`.`age` AS `age`, `mship_account`.`created` AS `created`, `mship_account`.`updated` AS `updated`, `mship_account`.`checked` AS `checked`, `mship_account`.`status` AS `status` FROM `mship_account` AS `mship_account` WHERE `1` = 1 LIMIT 10 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:02:53 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', 'Model_Account', Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1060): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1001): Kohana_ORM->_load_result(true)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(45): Kohana_ORM->find_all()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:28:47 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: cid ~ APPPATH/classes/Controller/Membership/Manage.php [ 57 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:47 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(57): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 57, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:48 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: cid ~ APPPATH/classes/Controller/Membership/Manage.php [ 57 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:48 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(57): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 57, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:49 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: cid ~ APPPATH/classes/Controller/Membership/Manage.php [ 57 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:49 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(57): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 57, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:56 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: name ~ APPPATH/classes/Controller/Membership/Manage.php [ 57 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:28:56 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(57): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 57, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:57
2013-07-21 16:30:27 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'CONCAT( name_first, ' ', name_last )' in 'where clause' [ SELECT `mship_account`.`id` AS `id`, `mship_account`.`token` AS `token`, `mship_account`.`token_ip` AS `token_ip`, `mship_account`.`name_first` AS `name_first`, `mship_account`.`name_last` AS `name_last`, `mship_account`.`password` AS `password`, `mship_account`.`extra_password` AS `extra_password`, `mship_account`.`gender` AS `gender`, `mship_account`.`age` AS `age`, `mship_account`.`created` AS `created`, `mship_account`.`updated` AS `updated`, `mship_account`.`checked` AS `checked`, `mship_account`.`status` AS `status` FROM `mship_account` AS `mship_account` WHERE `CONCAT( name_first, ' ', name_last )` LIKE '%Kieran%' LIMIT 10 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:30:27 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', 'Model_Account', Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1060): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1001): Kohana_ORM->_load_result(true)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(53): Kohana_ORM->find_all()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:30:29 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'CONCAT( name_first, ' ', name_last )' in 'where clause' [ SELECT `mship_account`.`id` AS `id`, `mship_account`.`token` AS `token`, `mship_account`.`token_ip` AS `token_ip`, `mship_account`.`name_first` AS `name_first`, `mship_account`.`name_last` AS `name_last`, `mship_account`.`password` AS `password`, `mship_account`.`extra_password` AS `extra_password`, `mship_account`.`gender` AS `gender`, `mship_account`.`age` AS `age`, `mship_account`.`created` AS `created`, `mship_account`.`updated` AS `updated`, `mship_account`.`checked` AS `checked`, `mship_account`.`status` AS `status` FROM `mship_account` AS `mship_account` WHERE `CONCAT( name_first, ' ', name_last )` LIKE '%Kieran%' LIMIT 10 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:30:29 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', 'Model_Account', Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1060): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1001): Kohana_ORM->_load_result(true)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(53): Kohana_ORM->find_all()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:30:49 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'CONCAT( name_first, ' ', name_last )' in 'where clause' [ SELECT `mship_account`.`id` AS `id`, `mship_account`.`token` AS `token`, `mship_account`.`token_ip` AS `token_ip`, `mship_account`.`name_first` AS `name_first`, `mship_account`.`name_last` AS `name_last`, `mship_account`.`password` AS `password`, `mship_account`.`extra_password` AS `extra_password`, `mship_account`.`gender` AS `gender`, `mship_account`.`age` AS `age`, `mship_account`.`created` AS `created`, `mship_account`.`updated` AS `updated`, `mship_account`.`checked` AS `checked`, `mship_account`.`status` AS `status` FROM `mship_account` AS `mship_account` WHERE `CONCAT( name_first, ' ', name_last )` LIKE '%k%' LIMIT 10 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:30:49 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', 'Model_Account', Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1060): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1001): Kohana_ORM->_load_result(true)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(53): Kohana_ORM->find_all()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:32:34 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'CONCAT( name_first, ' ', name_last )' in 'where clause' [ SELECT `mship_account`.`id` AS `id`, `mship_account`.`token` AS `token`, `mship_account`.`token_ip` AS `token_ip`, `mship_account`.`name_first` AS `name_first`, `mship_account`.`name_last` AS `name_last`, `mship_account`.`password` AS `password`, `mship_account`.`extra_password` AS `extra_password`, `mship_account`.`gender` AS `gender`, `mship_account`.`age` AS `age`, `mship_account`.`created` AS `created`, `mship_account`.`updated` AS `updated`, `mship_account`.`checked` AS `checked`, `mship_account`.`status` AS `status` FROM `mship_account` AS `mship_account` WHERE `CONCAT( name_first, ' ', name_last )` LIKE '%Kieran%' LIMIT 10 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:32:34 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `mship_a...', 'Model_Account', Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1060): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1001): Kohana_ORM->_load_result(true)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(53): Kohana_ORM->find_all()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#6 [internal function]: Kohana_Controller->execute()
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#11 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-21 16:53:23 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: total_pages ~ APPPATH/classes/Controller/Membership/Manage.php [ 73 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:73
2013-07-21 16:53:23 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(73): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 73, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:73
2013-07-21 16:55:42 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Model_Account::count() ~ APPPATH/classes/Controller/Membership/Manage.php [ 53 ] in :
2013-07-21 16:55:42 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 16:59:45 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: page ~ APPPATH/classes/Controller/Membership/Manage.php [ 78 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:78
2013-07-21 16:59:45 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(78): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 78, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->ajax_search()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:78
2013-07-21 17:00:02 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: page ~ APPPATH/classes/Controller/Membership/Manage.php [ 78 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:78
2013-07-21 17:00:02 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(78): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 78, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:78
2013-07-21 17:13:05 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Model_Account::count() ~ APPPATH/classes/Controller/Membership/Manage.php [ 52 ] in :
2013-07-21 17:13:05 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-21 17:25:00 --- EMERGENCY: ErrorException [ 8 ]: Object of class Database_MySQL_Result could not be converted to int ~ APPPATH/classes/Controller/Membership/Manage.php [ 53 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:53
2013-07-21 17:25:00 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(53): Kohana_Core::error_handler(8, 'Object of class...', '/var/www/dev.va...', 53, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:53
2013-07-21 17:25:33 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: total ~ APPPATH/classes/Controller/Membership/Manage.php [ 76 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:76
2013-07-21 17:25:33 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(76): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 76, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php:76
2013-07-21 18:01:49 --- EMERGENCY: ErrorException [ 2 ]: Missing argument 1 for Enum_Account_Qualification_Pilot::getDisplayString(), called in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php on line 95 and defined ~ APPPATH/classes/Enum/Account/Qualification/Pilot.php [ 41 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Enum/Account/Qualification/Pilot.php:41
2013-07-21 18:01:49 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Enum/Account/Qualification/Pilot.php(41): Kohana_Core::error_handler(2, 'Missing argumen...', '/var/www/dev.va...', 41, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(95): Enum_Account_Qualification_Pilot::getDisplayString()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Controller_Membership_Manage->ajax_search()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_search()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Enum/Account/Qualification/Pilot.php:41