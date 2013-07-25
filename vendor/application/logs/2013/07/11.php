<?php defined('SYSPATH') OR die('No direct script access.'); ?>

2013-07-11 11:06:46 --- EMERGENCY: View_Exception [ 0 ]: The requested view V3/Training/Status/Test could not be found ~ SYSPATH/classes/Kohana/View.php [ 257 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-07-11 11:06:46 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(137): Kohana_View->set_filename('V3/Training/Sta...')
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(30): Kohana_View->__construct('V3/Training/Sta...', NULL)
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Master.php(139): Kohana_View::factory('V3/Training/Sta...')
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Status.php(23): Controller_Master->setTemplate('Status/Test')
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Master.php(21): Controller_Training_Status->action_account()
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Master->action_index()
#6 [internal function]: Kohana_Controller->execute()
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Training_Status))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#11 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-07-11 11:07:04 --- EMERGENCY: View_Exception [ 0 ]: The requested view V3/Training/Status/Test could not be found ~ SYSPATH/classes/Kohana/View.php [ 257 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-07-11 11:07:04 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(137): Kohana_View->set_filename('V3/Training/Sta...')
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php(30): Kohana_View->__construct('V3/Training/Sta...', NULL)
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Master.php(139): Kohana_View::factory('V3/Training/Sta...')
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Status.php(23): Controller_Master->setTemplate('Status/Test')
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Training_Status->action_account()
#5 [internal function]: Kohana_Controller->execute()
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Training_Status))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#10 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/View.php:137
2013-07-11 11:09:07 --- EMERGENCY: ErrorException [ 1 ]: Class 'prarent' not found ~ APPPATH/classes/Controller/Training/Master.php [ 14 ] in :
2013-07-11 11:09:07 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-11 11:27:07 --- EMERGENCY: ErrorException [ 8 ]: Undefined property: Controller_Training_Theory::$GET ~ APPPATH/classes/Controller/Training/Theory.php [ 28 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php:28
2013-07-11 11:27:07 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php(28): Kohana_Core::error_handler(8, 'Undefined prope...', '/var/www/dev.va...', 28, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Training_Theory->action_take()
#2 [internal function]: Kohana_Controller->execute()
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Training_Theory))
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#7 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php:28
2013-07-11 11:28:35 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Controller_Training_Theory::param() ~ APPPATH/classes/Controller/Training/Theory.php [ 28 ] in :
2013-07-11 11:28:35 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-11 11:48:34 --- EMERGENCY: ErrorException [ 1 ]: Class 'Model_Training/Theory' not found ~ MODPATH/orm/classes/Kohana/ORM.php [ 46 ] in :
2013-07-11 11:48:34 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-11 11:49:30 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'train_training_theory.id' in 'where clause' [ SELECT `train_training_theory`.`test_id` AS `test_id`, `train_training_theory`.`test_name` AS `test_name`, `train_training_theory`.`atc_pilot` AS `atc_pilot`, `train_training_theory`.`min_qualification` AS `min_qualification`, `train_training_theory`.`test_type` AS `test_type`, `train_training_theory`.`question_config` AS `question_config`, `train_training_theory`.`require_approval_start` AS `require_approval_start`, `train_training_theory`.`require_approval_pass` AS `require_approval_pass`, `train_training_theory`.`automatic_retake` AS `automatic_retake`, `train_training_theory`.`available` AS `available` FROM `train_theory` AS `train_training_theory` WHERE `train_training_theory`.`id` = 'new' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-11 11:49:30 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `train_t...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(271): Kohana_ORM->find()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(46): Kohana_ORM->__construct('new')
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php(32): Kohana_ORM::factory('Training_Theory', 'new')
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Training_Theory->action_take()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Training_Theory))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-11 11:50:08 --- EMERGENCY: Database_Exception [ 1054 ]: Unknown column 'train_training_theory.id' in 'where clause' [ SELECT `train_training_theory`.`test_id` AS `test_id`, `train_training_theory`.`test_name` AS `test_name`, `train_training_theory`.`atc_pilot` AS `atc_pilot`, `train_training_theory`.`min_qualification` AS `min_qualification`, `train_training_theory`.`test_type` AS `test_type`, `train_training_theory`.`question_config` AS `question_config`, `train_training_theory`.`require_approval_start` AS `require_approval_start`, `train_training_theory`.`require_approval_pass` AS `require_approval_pass`, `train_training_theory`.`automatic_retake` AS `automatic_retake`, `train_training_theory`.`available` AS `available` FROM `train_theory` AS `train_training_theory` WHERE `train_training_theory`.`id` = 'new' LIMIT 1 ] ~ MODPATH/database/classes/Kohana/Database/MySQL.php [ 194 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-11 11:50:08 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php(251): Kohana_Database_MySQL->query(1, 'SELECT `train_t...', false, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(1069): Kohana_Database_Query->execute(Object(Database_MySQL))
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(976): Kohana_ORM->_load_result(false)
#3 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(271): Kohana_ORM->find()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/orm/classes/Kohana/ORM.php(46): Kohana_ORM->__construct('new')
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php(32): Kohana_ORM::factory('Training_Theory', 'new')
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Training_Theory->action_take()
#7 [internal function]: Kohana_Controller->execute()
#8 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Training_Theory))
#9 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#10 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#11 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#12 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/modules/database/classes/Kohana/Database/Query.php:251
2013-07-11 12:14:09 --- EMERGENCY: ErrorException [ 1 ]: Call to undefined method Model_Training_Theory::mayTake() ~ APPPATH/classes/Controller/Training/Theory.php [ 37 ] in :
2013-07-11 12:14:09 --- DEBUG: #0 [internal function]: Kohana_Core::shutdown_handler()
#1 {main} in :
2013-07-11 12:14:32 --- EMERGENCY: ErrorException [ 2 ]: Missing argument 1 for Model_Training_Theory::mayTake(), called in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php on line 37 and defined ~ APPPATH/classes/Model/Training/Theory.php [ 59 ] in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Model/Training/Theory.php:59
2013-07-11 12:14:32 --- DEBUG: #0 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Model/Training/Theory.php(59): Kohana_Core::error_handler(2, 'Missing argumen...', '/var/www/dev.va...', 59, Array)
#1 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Controller/Training/Theory.php(37): Model_Training_Theory->mayTake()
#2 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Controller.php(84): Controller_Training_Theory->action_take()
#3 [internal function]: Kohana_Controller->execute()
#4 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client/Internal.php(97): ReflectionMethod->invoke(Object(Controller_Training_Theory))
#5 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request/Client.php(114): Kohana_Request_Client_Internal->execute_request(Object(Request), Object(Response))
#6 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/system/classes/Kohana/Request.php(990): Kohana_Request_Client->execute(Object(Request))
#7 /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/index.php(118): Kohana_Request->execute()
#8 {main} in /var/www/dev.vatsim-uk.co.uk/bleed/core.vatsim-uk.co.uk/application/classes/Model/Training/Theory.php:59