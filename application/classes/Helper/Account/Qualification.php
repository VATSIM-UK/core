<?php 

defined('SYSPATH') or die('No direct script access.');

class Helper_Account_Qualification {
    /**
     * Convert a VATSIM rating value to a system rating value and type.
     * 
     * @param int $vrating The VATSIM rating.
     */
    public static function convert_vatsim_atc_rating($vrating){
        // First of all, if it's just a rating of OBS - C3, return that enum type!
        if($vrating >= 1 AND $vrating <= 7){
            return array("ATC", $vrating);
            //return "Enum_Account_Qualification_ATC::".Enum_Account_Qualification_ATC::valueToType($vrating);
        }
        
        // For ratings I1 - I3 (8,9,10) we need the Training ones!
        if($vrating == 8){
            return array("Training_ATC", Enum_Account_Qualification_Training_ATC::I1);
            //return "Enum_Account_Qualification_Training_ATC::".Enum_Account_Qualification_Training_ATC::valueToType($vrating-7);
        } elseif($vrating == 9){
            return array("Training_ATC", Enum_Account_Qualification_Training_ATC::I2);
        }elseif($vrating == 10){
            return array("Training_ATC", Enum_Account_Qualification_Training_ATC::I3);
        }
        
        // For ratings SUP and ADMIN (11 & 12) we need the ADMIN enum type!
        if($vrating == 11){
            return array("Admin", Enum_Account_Qualification_Admin::SUP);
            //return "Enum_Account_Qualification_Admin::SUP";
        } elseif($vrating == 12){
            return array("Admin", Enum_Account_Qualification_Admin::ADM);
            //return "Enum_Account_Qualification_Admin::ADM";
        }
        
        return array();
    }
}