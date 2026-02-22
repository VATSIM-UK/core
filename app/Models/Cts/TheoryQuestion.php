<?php

namespace App\Models\Cts;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheoryQuestion extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'theory_questions';

    public $timestamps = false;

    protected $fillable = [
        'level',
        'question',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'answer',
        'add_by',
        'add_date',
        'edit_by',
        'edit_date',
        'deleted',
        'status',
    ];

    public function answers(): string
    {
        return $this->hasMany(TheoryAnswer::class, 'question_id');
    }

    // The below matches the 'add_by' and 'edit_by' fields to the 'id' in the cts.members table. if no match is found for the id then it will match those fields with the cid.
    // The aim is to account for some cts accounts having mismatched IDs but core utilising the CID regardless on CRUD actions.
    protected function addedByMember(): Attribute
    {
        return Attribute::make(
            get: fn () => Member::where('id', $this->add_by)->orWhere('cid', $this->add_by)->first(),
        );
    }

    protected function editedByMember(): Attribute
    {
        return Attribute::make(
            get: fn () => Member::where('id', $this->edit_by)->orWhere('cid', $this->edit_by)->first(),
        );
    }
}
