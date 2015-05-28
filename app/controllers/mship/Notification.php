<?php

namespace Controllers\Mship;

use \Config;
use \Auth;
use \Request;
use \URL;
use \Input;
use \Session;
use \Redirect;
use \VatsimSSO;
use \Models\Mship\Account;
use \Models\Mship\Qualification as QualificationType;

class Notification extends \Controllers\BaseController {

    public function postAcknowledge($notification){
        $this->_account->readNotifications()->attach($notification);

        return Redirect::route("mship.notification.list");
    }

    public function getList() {
        // Get all unread notifications.
        $unreadNotifications = $this->_account->unread_notifications;
        $readNotifications = $this->_account->read_notifications;

        return $this->viewMake("mship.notification.list")
                    ->with("unreadNotifications", $unreadNotifications)
                    ->with("readNotifications", $readNotifications);
    }

}
