<?php

namespace App\Policies\Training;

use App\Models\Cts\TheoryQuestion;
use App\Models\Mship\Account;

class TheoryExamPolicy
{
    /**
     * Determine whether the user can view any question
     */
    public function viewAny(Account $account): bool
    {
        return $account->canAny([
            'theory-exams.questions.view.*',
            'theory-exams.questions.view.atc',
            'theory-exams.questions.view.pilot',
        ]);
    }

    /**
     * Determine whether the user can view the question
     * Need to look at making this specific to atc/pilot
     */
    public function view(Account $account, TheoryQuestion $question): bool
    {
        return $account->canAny([
            'theory-exams.questions.view.*',
            // 'theory-exams.questions.view.atc',
            // 'theory-exams.questions.view.pilot',
        ]);
    }

    /**
     * Determine whether the user can create questions
     */
    public function create(Account $account): bool
    {
        return $account->canAny([
            'theory-exams.questions.create.*',
            'theory-exams.questions.create.atc',
            'theory-exams.questions.create.pilot',
        ]);
    }

    /**
     * Determine whether the user can update questions
     * Need to look at making this specific to atc/pilot
     */
    public function update(Account $account, TheoryQuestion $question): bool
    {
        return $account->canAny([
            'theory-exams.questions.edit.*',
            // 'theory-exams.questions.edit.atc',
            // 'theory-exams.questions.edit.pilot',
        ]);
    }

    /**
     * Determine whether the user can delete questions
     * Need to look at making this specific to atc/pilot
     */
    public function delete(Account $account, TheoryQuestion $question): bool
    {
        return $account->canAny([
            'theory-exams.questions.delete.*',
            // 'theory-exams.questions.delete.atc',
            // 'theory-exams.questions.delete.pilot',
        ]);
    }
}
