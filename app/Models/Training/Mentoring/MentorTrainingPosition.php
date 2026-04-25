<?php

declare(strict_types=1);

namespace App\Models\Training\Mentoring;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MentorTrainingPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'mentorable_type',
        'mentorable_id',
        'created_by',
    ];

    public function mentorable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getTrainingPositionAttribute(): ?TrainingPosition
    {
        return $this->mentorable instanceof TrainingPosition ? $this->mentorable : null;
    }

    public function getQualificationAttribute(): ?Qualification
    {
        return $this->mentorable instanceof Qualification ? $this->mentorable : null;
    }

    public function getDisplayNameAttribute(): string
    {
        $model = $this->mentorable;
        if ($model instanceof TrainingPosition) {
            return $model->name ?? $model->position?->callsign ?? "Position {$model->id}";
        }
        if ($model instanceof Qualification) {
            return $model->name_long ?? $model->name ?? "Qualification {$model->id}";
        }

        return 'Unknown Item';
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'created_by');
    }
}
