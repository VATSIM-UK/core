<?php

namespace Tests\Feature\TrainingPanel;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Filament\Facades\Filament;
use Tests\TestCase;

abstract class BaseTrainingPanelTestCase extends TestCase
{
    protected Account $panelUser;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(
            Filament::getPanel('training')
        );

        $this->panelUser = Account::factory()->create(['id' => 9000000]);

        Member::factory()->create(['id' => $this->panelUser->id, 'cid' => $this->panelUser->id]);

        $this->panelUser->givePermissionTo('training.access');
    }
}
