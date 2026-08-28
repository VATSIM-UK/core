<?php

namespace App\Models\Events;

use App\Models\Atc\Position;
use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'manager_id',
        'eoi_published',
        'roster_published',
        'briefing_published',
        'briefing_created',
        'banner_created',
        'ecfmp_set_up',
        'my_vatsim_published',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'rostered' => 'boolean',
        'published_at' => 'datetime',
        'eoi_published' => 'boolean',
        'roster_published' => 'boolean',
        'briefing_published' => 'boolean',
        'briefing_created' => 'boolean',
        'banner_created' => 'boolean',
        'ecfmp_set_up' => 'boolean',
        'my_vatsim_published' => 'boolean',
    ];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'event_positions');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'manager_id');
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

    public function unpublishedChecklist(): array
    {
        $flags = [
            'EOI published' => $this->eoi_published,
            'Roster published' => $this->roster_published,
            'Briefing published' => $this->briefing_published,
            'Briefing created' => $this->briefing_created,
            'Banner created' => $this->banner_created,
            'ECFMP set up' => $this->ecfmp_set_up,
            'My.vatsim.net published' => $this->my_vatsim_published,
        ];

        return array_keys(array_filter($flags, fn (bool $done) => ! $done));
    }
}
