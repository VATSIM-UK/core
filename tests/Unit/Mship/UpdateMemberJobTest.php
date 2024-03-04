<?php

namespace Tests\Unit\Mship;

use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateMemberJobTest extends TestCase
{
    public function test_updates_home_members(): void
    {
        $existingMember = Account::factory()->create();
        $existingMember->cert_checked_at = Carbon::now()->subDay();
        $existingMember->addState(State::findByCode('DIVISION'));
        $existingMember->addQualification(Qualification::code('S2')->first());
        $existingMember->save();

        $url = config('vatsim-api.base')."members/{$existingMember->id}";
        Http::fake([
            $url => Http::response([
                'id' => $existingMember->id,
                'name_first' => 'Test',
                'name_last' => 'User',
                'rating' => Qualification::code('S3')->first()->vatsim,
                'pilotrating' => 0,
                'militaryrating' => 0,
                'division_id' => 'GBR',
                'region_id' => 'EMEA',
                'subdivision_id' => null,
                'reg_date' => '2018-02-04T09:22:20Z',
            ]),
        ]);

        UpdateMember::dispatchSync($existingMember->id);

        $existingMember = $existingMember->refresh();

        // test has updated the qualification of the member.
        $this->assertTrue($existingMember->hasQualification(Qualification::code('S3')->first()));

        // test updates name attributes.
        $this->assertEquals('Test', $existingMember->name_first);
        $this->assertEquals('User', $existingMember->name_last);

        $this->assertDatabaseHas('mship_account', [
            'id' => $existingMember->id,
            'cert_checked_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function test_updates_non_home_members()
    {
        $existingMember = Account::factory()->create();
        $existingMember->cert_checked_at = Carbon::now()->subDay();
        $existingMember->addState(State::findByCode('DIVISION'));
        $existingMember->addQualification(Qualification::code('S2')->first());
        $existingMember->save();

        $url = config('vatsim-api.base')."members/{$existingMember->id}";
        Http::fake([
            $url => Http::response([
                'id' => $existingMember->id,
                'rating' => Qualification::code('S3')->first()->vatsim,
                'pilotrating' => 0,
                'militaryrating' => 0,
                'division_id' => 'SEA',
                'region_id' => 'APAC',
                'subdivision_id' => null,
                'reg_date' => '2018-02-04T09:22:20Z',
            ]),
        ]);

        UpdateMember::dispatchSync($existingMember->id);

        $updatedMember = $existingMember->refresh();

        $this->assertTrue($updatedMember->hasQualification(Qualification::code('S3')->first()));

        $this->assertTrue($updatedMember->hasState(State::findByCode('INTERNATIONAL')));
        $this->assertFalse($updatedMember->hasState(State::findByCode('DIVISION')));

        $this->assertEquals('APAC', $updatedMember->primary_permanent_state->pivot->region);
        $this->assertEquals('SEA', $updatedMember->primary_permanent_state->pivot->division);

        // check we have not set the names or emails to null
        $this->assertEquals($existingMember->name_first, $updatedMember->name_first);
        $this->assertEquals($existingMember->name_last, $updatedMember->name_last);
        $this->assertEquals($existingMember->email, $updatedMember->email);

        $this->assertDatabaseHas('mship_account', [
            'id' => $updatedMember->id,
            'cert_checked_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function test_should_delete_member_when_api_returns_404()
    {
        $existingMember = Account::factory()->create();
        $existingMember->cert_checked_at = Carbon::now()->subDay();
        $existingMember->addState(State::findByCode('DIVISION'));
        $existingMember->addQualification(Qualification::code('S2')->first());
        $existingMember->save();

        $url = config('vatsim-api.base')."members/{$existingMember->id}";
        Http::fake([
            $url => Http::response('', 404),
        ]);

        UpdateMember::dispatchSync($existingMember->id);

        $this->assertSoftDeleted('mship_account', [
            'id' => $existingMember->id,
        ]);
    }
}
