<?php

namespace App\Models\Atc;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model implements Endorseable
{
    use HasFactory;

    protected $fillable = [
        'callsign',
        'name',
        'frequency',
        'type',
        'sub_station',
        'temporarily_endorsable',
    ];

    protected $casts = [
        'sub_station' => 'boolean',
    ];

    const TYPE_ATIS = 1;

    const TYPE_DELIVERY = 2;

    const TYPE_GROUND = 3;

    const TYPE_TOWER = 4;

    const TYPE_APPROACH = 5;

    const TYPE_ENROUTE = 6;

    const TYPE_TERMINAL = 7;

    const TYPE_FSS = 8;

    public function airports(): BelongsToMany
    {
        return $this->belongsToMany(Airport::class, 'airport_positions');
    }

    public function getMinimumVatsimQualificationAttribute()
    {
        return match ($this->type) {
            'Ground', 'Delivery', 'ATIS' => 2,
            'Tower' => 3,
            'Approach/Radar' => 4,
            'FSS', 'Terminal Control', 'Enroute' => 5,
            default => 0,
        };
    }

    public function getTypeAttribute(int $type): string
    {
        switch ($type) {
            case self::TYPE_ATIS:
                return 'ATIS';
            case self::TYPE_DELIVERY:
                return 'Delivery';
            case self::TYPE_GROUND:
                return 'Ground';
            case self::TYPE_TOWER:
                return 'Tower';
            case self::TYPE_APPROACH:
                return 'Approach/Radar';
            case self::TYPE_ENROUTE:
                return 'Enroute';
            case self::TYPE_TERMINAL:
                return 'Terminal Control';
            case self::TYPE_FSS:
                return 'Flight Service Stations';
            default:
                return 'Unknown';
        }
    }

    public function isTemporarilyEndorsable(): bool
    {
        return $this->temporarily_endorsable ?? false;
    }

    public function scopeTemporarilyEndorsable(Builder $query): Builder
    {
        return $query->where('temporarily_endorsable', true);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->callsign;
    }
}
