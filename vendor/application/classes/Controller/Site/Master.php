<?php

defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Site_Master extends Controller_Master {
    // User data.
    protected $_account = NULL;
    
    protected function getDefaultAction() {
        return "homepage";
    }
}