<?php

namespace Tests\Unit\Mship;

use App\Enums\QualificationTypeEnum;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PilotVirtualQualificationTest extends TestCase
{
    use DatabaseTransactions;

    private function tfpQualification(): Qualification
    {
        return Qualification::firstWhere('code', 'TFP')
            ?? Qualification::factory()->pilotVirtual()->create([
                'code' => 'TFP',
                'name_small' => 'TFP',
                'name_long' => 'The Flying Programme',
                'name_grp' => 'The Flying Programme',
            ]);
    }

    #[Test]
    public function it_has_a_tfp_pilot_virtual_catalog_row(): void
    {
        $qualification = $this->tfpQualification();

        $this->assertSame(QualificationTypeEnum::PilotVirtual->value, $qualification->type);
        $this->assertSame(0, $qualification->vatsim);
        $this->assertTrue(Qualification::pilotTrainable()->whereKey($qualification->id)->exists());
        $this->assertTrue($qualification->isPilotTrainable());
    }

    #[Test]
    public function it_can_grant_and_revoke_a_virtual_pilot_qualification_locally(): void
    {
        $account = Account::factory()->create();
        $qualification = $this->tfpQualification();

        $account->addQualification($qualification);

        $this->assertTrue($account->fresh()->hasQualification($qualification));
        $this->assertTrue($account->fresh()->qualifications_pilot_virtual->contains('id', $qualification->id));
        $this->assertTrue($account->fresh()->active_qualifications->contains('id', $qualification->id));

        $account->removeQualification($qualification);

        $this->assertFalse($account->fresh()->hasQualification($qualification));
        $this->assertFalse($account->fresh()->qualifications_pilot_virtual->contains('id', $qualification->id));
    }

    #[Test]
    public function update_vatsim_ratings_does_not_remove_virtual_pilot_qualifications(): void
    {
        $account = Account::factory()->create();
        $tfp = $this->tfpQualification();

        $account->addQualification($tfp);
        $account->updateVatsimRatings(1, 0);

        $this->assertTrue($account->fresh()->hasQualification($tfp));
    }

    #[Test]
    public function update_member_job_does_not_remove_virtual_pilot_qualifications(): void
    {
        $account = Account::factory()->create();
        $account->cert_checked_at = Carbon::now()->subDay();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification(Qualification::code('S1')->first());
        $account->addQualification(Qualification::code('PPL')->first());
        $account->addQualification($this->tfpQualification());
        $account->save();

        $url = config('services.vatsim-net.api.base')."members/{$account->id}";
        Http::fake([
            $url => Http::response([
                'id' => $account->id,
                'name_first' => 'Test',
                'name_last' => 'User',
                'rating' => Qualification::code('S1')->first()->vatsim,
                'pilotrating' => 0,
                'militaryrating' => 0,
                'division_id' => 'GBR',
                'region_id' => 'EMEA',
                'subdivision_id' => null,
                'reg_date' => '2018-02-04T09:22:20Z',
            ]),
        ]);

        UpdateMember::dispatchSync($account->id);

        $account = $account->fresh();

        $this->assertFalse($account->hasQualification(Qualification::code('PPL')->first()));
        $this->assertTrue($account->hasQualification($this->tfpQualification()));
    }
}
