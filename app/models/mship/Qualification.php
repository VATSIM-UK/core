<?php

namespace Models\Mship;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Carbon\Carbon;

class Qualification extends \Eloquent {
	use SoftDeletingTrait;
        protected $table = "mship_qualification";
        protected $primaryKey = "qualification_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $hidden = ['qualification_id'];

        public static function parseVatsimATCQualification($network){
            if($network < 1){
                return null;
            } elseif($network >= 8 AND $network <= 10){
                $type = "training_atc";
            } elseif($network >= 11){
                $type = "admin";
            }

            // Sort out the pilot ratings
            $netQ = Qualification::where("type", "=", "atc")->where("vatsim", "=", $network)->first();
            return $netQ;
        }

        public static function parseVatsimPilotQualifications($network){
            $ratingsOutput = array();

            // Let's check each bitmask....
            for($i=0; $i<=8; $i++){
                $pow = pow(2, $i);
                if(($pow & $network) == $pow){
                    $ro = Qualification::where("type", "=", "pilot")->where("vatsim", "=", $pow)->first();
                    if($ro){
                        $ratingsOutput[] = $ro;
                    }
                }
            }

            return $ratingsOutput;
        }
}
