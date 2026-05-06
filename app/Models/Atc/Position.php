<?php

namespace App\Models\Atc;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;

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
        'virtual',
    ];

    protected $casts = [
        'sub_station' => 'boolean',
        'virtual' => 'boolean',
    ];

    const TYPE_ATIS = 1;

    const TYPE_DELIVERY = 2;

    const TYPE_GROUND = 3;

    const TYPE_TOWER = 4;

    const TYPE_APPROACH = 5;

    const TYPE_ENROUTE = 6;

    const TYPE_TERMINAL = 7;

    const TYPE_FSS = 8;

    public static function typeOptions(): array
    {
        return [
            self::TYPE_ATIS => 'ATIS',
            self::TYPE_DELIVERY => 'Delivery',
            self::TYPE_GROUND => 'Ground',
            self::TYPE_TOWER => 'Tower',
            self::TYPE_APPROACH => 'Approach/Radar',
            self::TYPE_ENROUTE => 'Enroute',
            self::TYPE_TERMINAL => 'Terminal Control',
            self::TYPE_FSS => 'Flight Service Stations',
        ];
    }

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
        return static::typeOptions()[$type] ?? 'Unknown';
    }

    public function isVirtual(): bool
    {
        return $this->virtual ?? false;
    }

    public function isTemporarilyEndorsable(): bool
    {
        return $this->temporarily_endorsable ?? false;
    }

    public function scopeVirtual(Builder $query): Builder
    {
        return $query->where('virtual', true);
    }

    public function scopeReal(Builder $query): Builder
    {
        return $query->where('virtual', false);
    }

    public function scopeTemporarilyEndorsable(Builder $query): Builder
    {
        return $query->where('temporarily_endorsable', true);
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getRawOriginal('name')
        );
    }

    public function description(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->callsign
        );
    }

    protected function rts(): Attribute
    {
        // use the position callsign to determine the rts for the position.
        // the callsign is in the format of EGXX_TWR, EGXX_APP, EGXX_CTR
        $mapping = [
            'PT3' => 14,
            'GND' => 14,
            'TWR' => 18,
            'APP' => 19,
            'CTR' => 17,
        ];

        $callsignParts = explode('_', $this->callsign);
        $last = Arr::last($callsignParts);
        $rts = $mapping[$last] ?? null;

        return Attribute::make(
            get: fn () => $rts,
        );
    }

    protected function examLevel(): Attribute
    {
        $mapping = [
            'GND' => 'OBS',
            'TWR' => 'TWR',
            'APP' => 'APP',
            'CTR' => 'CTR',
            'PT3' => 'OBS',
        ];

        return Attribute::make(
            get: fn () => $mapping[Arr::last(explode('_', $this->callsign))] ?? null,
        );
    }
}
