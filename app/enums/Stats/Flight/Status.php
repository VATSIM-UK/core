<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Stats_Flight_Status {
    const UNKNOWN = 0;
    const PRE_FLIGHT = 1;
    const TAXI_OUT = 2;
    const DEPARTING = 3;
    const CLIMB = 4;
    const CRUISE = 5;
    const DESCENT = 6;
    const APPROACH = 7;
    const TAXI_IN = 8;
    const ARRIVED = 9;
    
    public static function getType($id, $default='ATC'){
        switch($id){
             case self::PRE_FLIGHT:
                  return 'Pre-Flight/On-Blocks';
             case self::TAXI_OUT:
                  return 'Outbound Taxi';
             case self::DEPARTING:
                  return "Departing";
             case self::CLIMB:
                  return "Climbing";
             case self::CRUISE:
                  return "Cruising";
             case self::DESCENT:
                  return "Descending";
             case self::APPROACH:
                  return "Approaching Destination";
             case self::TAXI_IN:
                  return "Inbound Taxi";
             case self::TAXI_IN:
                  return "Arrived/On-Blocks";
             default:
                  return false;
        }
    }
    
}