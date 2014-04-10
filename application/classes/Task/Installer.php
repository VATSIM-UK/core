<?php defined('SYSPATH') or die('No direct script access.');
 
class Task_Installer extends Minion_Task
{
    protected $_options = array(
        "debug" => false,
    );
    
    protected function _execute(array $params)
    {
        // Disable so output is instant & log starting.
        ob_end_flush();
        Log::instance()->add(Log::INFO, "Task::Installer started.");
        
        // Get the DB version
        $_dbVersion = ORM::factory("Setting")->getValue("system.version.current");
        
        // Triple check there's a difference
        if($_dbVersion == Enum_Main::CURRENT_VERSION){
            Log::instance()->add(Log::INFO, "Task::Installer doesn't need to run.  Finished.");
            return;
        }
        
        // See if there's an install file to run.
        while($ugFiles = glob(APPPATH."install/sql/".$_dbVersion."-*.sql")){
            $ugFile_raw = $ugFiles[0];
            $ugFile = str_replace(APPPATH."install/sql/", "", $ugFile_raw);
            preg_match("/".$_dbVersion."\-([0-9]+\.[0-9]+\.[0-9]+)\.sql/i", $ugFile, $matches);
            
            // Get the "to" version number.
            if(!isset($matches[1])){
                Log::instance()->add(Log::INFO, "Task::Installer running upgrade ".$_dbVersion."-".$expVersion.".");
                return;
            }
            $expVersion = $matches[1];
            
            // Now let's run the upgrades!
            print("Task::Installer upgrading from version ".$_dbVersion." to ".$expVersion.".\n");
            Log::instance()->add(Log::INFO, "Task::Installer upgrading from version ".$_dbVersion." to ".$expVersion.".");
            
            $upgradeSQLs = file_get_contents($ugFile_raw);
            
            if(empty($upgradeSQLs) OR $upgradeSQLs == ""){
                Log::instance()->add(Log::INFO, "Task::Installer unable to upgrade using empty file.");
                return;
            }
            
            DB::query(null,$upgradeSQLs)->execute();
            
            // Now get the "latest" version and confirm it installed.
            $_dbVersion = ORM::factory("Setting")->getValue("system.version.current");
            
            if($_dbVersion != $expVersion){
                Log::instance()->add(Log::INFO, "Task::Installer error in script - upgrade seemed to file, check logs.");
                return;
            }
            
            Log::instance()->add(Log::INFO, "Task::Installer upgrade ".$_dbVersion."-".$expVersion." completed successfully.");
        }
        
        // Log the finish.
        Log::instance()->add(Log::INFO, "Task::Postmaster::Parse finished.");
    }
}