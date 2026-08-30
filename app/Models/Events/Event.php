<?php

namespace App\Models\Events;

use App\Enums\EventChecklistItem;
use App\Models\Atc\Position;
use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tagline',
        'description',
        'image_url',
        'start',
        'end',
        'rostered',
        'published_at',
        'published_by',
        'manager_id',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'rostered' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'event_positions');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'manager_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'published_by');
    }

    public function checklistCompletions(): HasMany
    {
        return $this->hasMany(EventChecklistCompletion::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('end', '>=', now());
    }

    public function isDraft(): bool
    {
        return $this->published_at === null;
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * @return array<int, string> the ticked items, as their enum values
     */
    public function completedChecklistItems(): array
    {
        return $this->checklistCompletions
            ->map(fn (EventChecklistCompletion $completion): string => $completion->item->value)
            ->all();
    }

    public function completionFor(EventChecklistItem $item): ?EventChecklistCompletion
    {
        return $this->checklistCompletions
            ->first(fn (EventChecklistCompletion $completion): bool => $completion->item === $item);
    }

    public function hasCompleted(EventChecklistItem $item): bool
    {
        return $this->completionFor($item) !== null;
    }

    /**
     * @return array<int, string> labels of the outstanding items, in enum order
     */
    public function unpublishedChecklist(): array
    {
        $completed = $this->completedChecklistItems();

        return array_values(array_map(
            fn (EventChecklistItem $item): string => $item->label(),
            array_filter(
                EventChecklistItem::cases(),
                fn (EventChecklistItem $item): bool => ! in_array($item->value, $completed, true),
            ),
        ));
    }
}
