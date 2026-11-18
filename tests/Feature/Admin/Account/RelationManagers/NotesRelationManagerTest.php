<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\Accounts\RelationManagers\NotesRelationManager;
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

        $this->assertDatabaseHas('mship_account_note', ['account_id' => $account->id, 'writer_id' => $this->privacc->id, 'content' => 'the content', 'attachment_id' => null, 'attachment_type' => null, 'note_type_id' => $generalTypeId]);
    }

    public function test_it_hides_internal_notes_without_sensitive_permission()
    {
        $account = Account::factory()->create();
        $generalNote = $account->addNote(Type::where('name', 'General')->first(), 'a public note', $this->adminUser);
        $internalNote = $account->addNote('discipline', 'an internal ban note', $this->adminUser);

        $this->actingAsAdminUser('account.view-insensitive.*');

        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $account, 'pageClass' => ViewRecord::class])
            ->assertCanSeeTableRecords([$generalNote])
            ->assertCanNotSeeTableRecords([$internalNote]);

        $this->adminUser->givePermissionTo('account.view-sensitive.*');

        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $account, 'pageClass' => ViewRecord::class])
            ->assertCanSeeTableRecords([$generalNote, $internalNote]);
    }
}
