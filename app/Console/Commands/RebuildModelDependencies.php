<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Models\Sys\Postmaster\Template as PostmasterTemplates;
use App\Models\Sys\Timeline\Action as TimelineActions;

class RebuildModelDependencies extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rebuild:model-dependencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the necessary commands after a new installation/update.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        $this->updateModelDependencies();
    }

    private function updateModelDependencies() {
        $rdi = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path() . "/models"));
        foreach ($rdi as $current) {
            $fileName = basename($current, ".php");
            $realModel = str_replace(app_path() . "/models/", "", $current);

            // Ignore dot files, old files, abstract or interface.
            if ($fileName[0] == "." OR substr($realModel, 0, 2) == "_o" OR $fileName[0] == "a" OR $fileName[0] == "i") {
                continue;
            }

            // Make the real model more... real
            $realModel = str_replace("/", "_", $realModel);
            $realModel = basename($realModel, ".php");
            $realModel = strtoupper($realModel);

            $section = substr($realModel, 0, strpos($realModel, "_"));
            $area = substr($realModel, (strpos($realModel, "_") == 0 ? 0 : strpos($realModel, "_")+1));

            /** Insert all postmaster templates. **/
            /*$actions = array("CREATED", "UPDATED", "DELETED");
            foreach($actions as $a){
                $exists = PostmasterTemplates::where("area", "LIKE", $area)
                                             ->where("key", "LIKE", $key)
                                             ->where("action", "LIKE", $a)
                                             ->count();
                if($exists < 1){
                    $pmt = new PostmasterTemplates();
                    $pmt->area = $area;
                    $pmt->key = $key;
                    $pmt->action = $a;
                    $pmt->save();
                }
            }*/

            /** Insert all timeline actions. **/
            $actions = array("CREATED", "UPDATED", "DELETED");
            foreach($actions as $a){
                $exists = TimelineActions::where("section", "LIKE", $section)
                                         ->where("area", "LIKE", $area)
                                         ->where("action", "LIKE", $a)
                                         ->count();
                if($exists < 1){
                    $tla = new TimelineActions();
                    $tla->section = $section;
                    $tla->area = $area;
                    $tla->action = $a;
                    $tla->save();
                }
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments() {
        return array(
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
        );
    }

}
