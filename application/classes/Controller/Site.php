<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Site extends Controller_Master {

    protected $_templateDir = "V2";
    protected $_templateOverride = true;

    public function action_index() {

        $this->setTitle('VATSIM United Kingdom Division');
    }

}
