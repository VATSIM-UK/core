<?php

namespace Tests\Unit\Training\WaitingList;

use App\Events\Training\AccountNoteChanged;
use App\Listeners\Training\WaitingList\LogNoteChanged;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use TiMacDonald\Log\LogFake;

class WaitingListLoggingTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private $waitingList;
    private $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();

        $this->account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($this->account, $this->privacc);
    }
    
    /** @test */
    public function itLogsTheChangeInContentForNotesInLists()
    {
        Log::swap(new LogFake);

        $waitingListAccount = $this->waitingList->accounts->find($this->account->id)->pivot;

        $event = $this->mock(AccountNoteChanged::class);
        $event->waitingListAccount = $waitingListAccount;
        $event->account = $this->account;
        $event->oldNoteContent = null;
        $event->newNoteContent = 'This is a note';

        $listener = app()->make(LogNoteChanged::class);
        $listener->handle($event);

        Log::channel('training')->assertLoggedMessage('info',
            "A note about {$this->account->name} ({$this->account->id}) in waiting list {$this->waitingList->name} ({$this->waitingList->id}) was changed from 
            {$event->oldNoteContent} to {$event->newNoteContent}");
    }
}
