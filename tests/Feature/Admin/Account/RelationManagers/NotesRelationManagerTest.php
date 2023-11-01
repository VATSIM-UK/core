<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Resources\AccountResource\RelationManagers\NotesRelationManager;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class NotesRelationManagerTest extends BaseAdminTestCase
{
    public function test_it_renders()
    {
        $this->actingAsSuperUser();

        $account = Account::factory()->create();
        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $account, 'pageClass' => ViewRecord::class])
            ->assertSuccessful();
    }

    public function test_it_can_create_note()
    {
        $this->actingAsSuperUser();

        $account = Account::factory()->create();
        $generalTypeId = Type::where('name', 'General')->first()->id;
        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $account, 'pageClass' => ViewRecord::class])
            ->callTableAction('create', null, ['content' => 'the content', 'type' => $generalTypeId]);

        $this->assertDatabaseHas('mship_account_note', ['account_id' => $account->id, 'writer_id' => $this->privacc->id, 'content' => 'the content', 'attachment_id' => $account->id, 'attachment_type' => Account::class, 'note_type_id' => $generalTypeId]);
    }
}
