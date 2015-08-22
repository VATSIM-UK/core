<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipTest extends TestCase
{

    use DatabaseTransactions;

    public function testLandingPage(){
        $this->visit('/')
             ->see('This page will redirect you automatically');
    }
    public function testDashboard(){
        $user = factory(App\Models\Mship\Account::class, "normal")->make();

        $this->actingAs($user)
             ->visit("/mship/manage/dashboard")
             ->see("Below are the current details stored by the Single Sign-On (SSO) system.");
        $this->dump();
    }
    public function testAddSecondaryEmailSuccess(){
        $this->visit("/mship/auth/login");
        $this->dump();
    }
}