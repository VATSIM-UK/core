<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Mship_Master extends Controller_Master {
    protected $_templateDir = "Standalone"; // Override parent settings.

    public function __construct($request, $response){
        parent::__construct($request, $response);
    }
}