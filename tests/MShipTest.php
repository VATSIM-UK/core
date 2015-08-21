<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipTest extends BaseTest
{

    use DatabaseTransactions;

    public function testLandingPage(){
        $this->visit('/')
             ->see('This page will redirect you automatically');
    }
    public function testAddSecondaryEmailSuccess(){
        $this->visit("/mship/auth/login");
        $this->dump();
    }
}