<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-06-14 14:18:21 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: remember ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php [ 78 ] in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php:78
2013-06-14 14:18:21 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php(78): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 78, Array)
#1 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php:78
2013-06-14 14:25:52 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: remember ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php [ 78 ] in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php:78
2013-06-14 14:25:52 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php(78): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 78, Array)
#1 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php:78
2013-06-14 14:26:07 --- EMERGENCY: ErrorException [ 1 ]: Class 'prarent' not found ~ APPPATH/classes/Controller/Account/Master.php [ 16 ] in :
2013-06-14 14:26:07 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 14:26:15 --- EMERGENCY: ErrorException [ 8 ]: Undefined index: remember ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php [ 78 ] in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php:78
2013-06-14 14:26:15 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php(78): Kohana_Core::error_handler(8, 'Undefined index...', '/var/www/dev.va...', 78, Array)
#1 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/home/login.php:78
2013-06-14 14:27:44 --- EMERGENCY: ErrorException [ 8 ]: Undefined variable: rc ~ /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/admin/members.php [ 1060 ] in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/admin/members.php:1060
2013-06-14 14:27:44 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/admin/members.php(1060): Kohana_Core::error_handler(8, 'Undefined varia...', '/var/www/dev.va...', 1060, Array)
#1 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/rts.vatsim-uk.co.uk/admin/members.php:1060
2013-06-14 15:40:46 --- EMERGENCY: ErrorException [ 1 ]: Class 'prarent' not found ~ APPPATH/classes/Controller/Account/Master.php [ 16 ] in :
2013-06-14 15:40:46 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 16:06:35 --- EMERGENCY: View_Exception [ 0 ]: The requested view V3/Membership/Account/ManageUser could not be found ~ SYSPATH/classes/Kohana/View.php [ 257 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-06-14 16:06:35 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(137): Kohana_View->set_filename('V3/Membership/A...')
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(30): Kohana_View->__construct('V3/Membership/A...', NULL)
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Master.php(139): Kohana_View::factory('V3/Membership/A...')
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(27): Controller_Master->setTemplate('Account/ManageU...')
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_user()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-06-14 16:16:59 --- EMERGENCY: View_Exception [ 0 ]: The requested view Manage/User could not be found ~ SYSPATH/classes/Kohana/View.php [ 257 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-06-14 16:16:59 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(137): Kohana_View->set_filename('Manage/User')
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(30): Kohana_View->__construct('Manage/User', NULL)
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Membership/Manage.php(32): Kohana_View::factory('Manage/User')
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Membership_Manage->action_ajax_test()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Membership_Manage))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-06-14 20:25:04 --- EMERGENCY: ErrorException [ 1 ]: Undefined class constant 'ACTION_STATE_USER' ~ APPPATH/classes/Helper/Membership/Account.php [ 331 ] in :
2013-06-14 20:25:04 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 20:25:13 --- EMERGENCY: ErrorException [ 1 ]: Undefined class constant 'ACTION_STATE_USER' ~ APPPATH/classes/Helper/Membership/Account.php [ 331 ] in :
2013-06-14 20:25:13 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 20:52:24 --- EMERGENCY: ErrorException [ 1 ]: Using $this when not in object context ~ MODPATH/vatsim/classes/Vatsim/Autotools.php [ 109 ] in :
2013-06-14 20:52:24 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 20:53:05 --- EMERGENCY: ErrorException [ 1 ]: Using $this when not in object context ~ MODPATH/vatsim/classes/Vatsim/Autotools.php [ 109 ] in :
2013-06-14 20:53:05 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 22:15:06 --- EMERGENCY: Database_Exception [ 1062 ]: Duplicate entry '0' for key 'PRIMARY' [ INSERT INTO `mship_account` (`name_first`, `name_last`, `status`) VALUES ('Vatuk', 'System', 7) ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-06-14 22:15:06 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(2, 'INSERT INTO `ms...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1321): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1418): Kohana_ORM->create(NULL)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/bootstrap.php(138): Kohana_ORM->save()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(102): require('/var/www/dev.va...')
#5 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-06-14 22:15:11 --- EMERGENCY: Database_Exception [ 1062 ]: Duplicate entry '0' for key 'PRIMARY' [ INSERT INTO `mship_account` (`name_first`, `name_last`, `status`) VALUES ('Vatuk', 'System', 7) ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-06-14 22:15:11 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(2, 'INSERT INTO `ms...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1321): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1418): Kohana_ORM->create(NULL)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/bootstrap.php(138): Kohana_ORM->save()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(102): require('/var/www/dev.va...')
#5 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-06-14 22:16:38 --- EMERGENCY: Database_Exception [ 1062 ]: Duplicate entry '0' for key 'PRIMARY' [ INSERT INTO `mship_account` (`name_first`, `name_last`, `status`) VALUES ('Vatuk', 'System', 7) ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-06-14 22:16:38 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(2, 'INSERT INTO `ms...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1321): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1418): Kohana_ORM->create(NULL)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/bootstrap.php(138): Kohana_ORM->save()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(102): require('/var/www/dev.va...')
#5 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-06-14 22:31:09 --- EMERGENCY: ErrorException [ 1 ]: Cannot break/continue 1 level ~ APPPATH/classes/Helper/Membership/Account.php [ 145 ] in :
2013-06-14 22:31:09 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 22:46:38 --- EMERGENCY: ErrorException [ 1 ]: Cannot break/continue 1 level ~ APPPATH/classes/Helper/Membership/Account.php [ 178 ] in :
2013-06-14 22:46:38 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 22:46:46 --- EMERGENCY: ErrorException [ 1 ]: Cannot break/continue 1 level ~ APPPATH/classes/Helper/Membership/Account.php [ 178 ] in :
2013-06-14 22:46:46 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-06-14 23:07:37 --- EMERGENCY: ErrorException [ 4096 ]: Method Model_Account_Email::__toString() must return a string value ~ SYSPATH/classes/Kohana/Cookie.php [ 158 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php:158
2013-06-14 23:07:37 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php(158): Kohana_Core::error_handler(4096, 'Method Model_Ac...', '/var/www/dev.va...', 158, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php(115): Kohana_Cookie::salt('vuk_sso_email', Object(Model_Account_Email))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Account/Session.php(102): Kohana_Cookie::set('vuk_sso_email', Object(Model_Account_Email), 60)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Account_Session->action_login()
#4 [internal function]: Kohana_Controller->execute()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Account_Session))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#9 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Cookie.php:158