<?php

namespace App\Models\Training;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPlace extends Model
{
    use HasFactory;

    protected $casts = [
        'places' => 'integer',
    ];

    protected $fillable = [
        'training_position_id',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function trainingPosition(): BelongsTo
    {
        return $this->belongsTo(TrainingPosition::class);
    }
}
