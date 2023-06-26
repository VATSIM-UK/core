<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\Note\Type;
use App\Services\BaseService;
use Illuminate\Contracts\Auth\Authenticatable;

class AddNote implements BaseService
{
    protected $account;

    protected $noteType;

    protected $noteTaker;

    protected $noteContent;

    public function __construct(Account $account, Type $noteType, Authenticatable $noteTaker, $noteContent)
    {
        $this->account = $account;
        $this->noteType = $noteType;
        $this->noteTaker = $noteTaker;
        $this->noteContent = $noteContent;
    }

    public function handle()
    {
        $this->account->addNote($this->noteType, $this->noteContent, $this->noteTaker);
    }
}
