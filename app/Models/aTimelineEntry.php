<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use App\Models\Sys\Timeline\Entry;
use App\Models\Mship\Account;
use Session;
use Input;

abstract class aTimelineEntry extends \App\Models\aModel implements \iTimelineEntry {

    public static function eventLog($logKey, $model, $extra=null, $data=null){
        if($extra == null){
            $extra = Account::find(Session::get("auth_account", 0));
            if(!$extra){
                $extra = Account::find(Session::get("auth_adm_account", 0));
            }
        }

        if($data == null){
            $data = Input::all();
        }

        Entry::log($logKey, $model, $extra, $data);
        Entry::log($logKey."_BY", $extra, $model, $data);
    }

    public static function eventCreated($model, $extra=null, $data=null) {
        parent::eventCreated($model);

        /*$logKey = $model->getTable();
        $logKey = strtoupper($logKey);
        $logKey.= "_CREATED";

        self::eventLog($logKey, $model, $extra, $data);*/
    }

    public static function eventUpdated($model, $extra=null, $data=null) {
        parent::eventCreated($model);

        /*$logKey = $model->getTable();
        $logKey = strtoupper($logKey);
        $logKey.= "_UPDATED";

        self::eventLog($logKey, $model, $extra, $data);*/
    }

    public static function eventDeleted($model, $extra=null, $data=null) {
        parent::eventCreated($model);

        /*$logKey = $model->getTable();
        $logKey = strtoupper($logKey);
        $logKey.= "_DELETED";

        self::eventLog($logKey, $model, $extra, $data);*/
    }

    public function timelineEntriesOwner() {
        return $this->morphMany("\App\Models\Sys\Timeline\Entry", "owner");
    }

    public function timelineEntriesExtra() {
        return $this->morphMany("\App\Models\Sys\Timeline\Entry", "extra");
    }

    public function getTimelineEntriesRecentAttribute() {
        $owner = $this->timelineEntriesOwner()->orderBy("created_at", "DESC")->limit(25)->get();
        $extra = $this->timelineEntriesExtra()->orderBy("created_at", "DESC")->limit(25)->get();

        $recent = $owner->merge($extra);
        $recent = $recent->sort(function($a, $b) {
            $a = \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $a->created_at);
            $b = \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $b->created_at);

            if ($a->eq($b)) {
                return 0;
            } else {
                return ($a->gt($b) ? -1 : 1);
            }
        });
        $recent = $recent->slice(0, 25);
        return $recent;
    }

}
