<?php

namespace App\Models\Atc;

use App\Models\Airport;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use RuntimeException;

class Position extends Model implements Endorseable
{
    use HasFactory;
    use SoftDeletes;

    public static bool $bypassUkcpProtection = false;

    protected $fillable = [
        'callsign',
        'name',
        'frequency',
        'type',
        'temporarily_endorsable',
        'virtual',
        'ukcp_position_id',
        'top_down',
    ];

    protected $casts = [
        'virtual' => 'boolean',
        'ukcp_position_id' => 'integer',
        'top_down' => 'json',
    ];

    const TYPE_ATIS = 1;

    const TYPE_DELIVERY = 2;

    const TYPE_GROUND = 3;

    const TYPE_TOWER = 4;

    const TYPE_APPROACH = 5;

    const TYPE_ENROUTE = 6;

    const TYPE_TERMINAL = 7;

    const TYPE_FSS = 8;

    protected static function booted(): void
    {
        static::saving(function (Position $position) {
            if (static::$bypassUkcpProtection) {
                return;
            }

            if ($position->ukcp_position_id !== null && $position->exists) {
                if ($position->isDirty('callsign')) {
                    throw new RuntimeException('Cannot modify callsign on a UKCP-synced position.');
                }

                if ($position->isDirty('frequency')) {
                    throw new RuntimeException('Cannot modify frequency on a UKCP-synced position.');
                }
            }
        });
    }

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

    public function positionGroups(): BelongsToMany
    {
        return $this->belongsToMany(PositionGroup::class, 'position_group_positions', 'position_id', 'position_group_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getMinimumVatsimQualificationAttribute()
    {
        return static::minimumVatsimRatingForType($this->getRawOriginal('type'));
    }

    public static function minimumVatsimRatingForType(int $type): int
    {
        return match ($type) {
            self::TYPE_DELIVERY, self::TYPE_GROUND, self::TYPE_ATIS => 2,
            self::TYPE_TOWER => 3,
            self::TYPE_APPROACH => 4,
            self::TYPE_FSS, self::TYPE_TERMINAL, self::TYPE_ENROUTE => 5,
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

    public function scopeSynced(): Builder
    {
        return $this->whereNotNull('ukcp_position_id');
    }

    public function scopeCoreNative(): Builder
    {
        return $this->whereNull('ukcp_position_id');
    }

    /**
     * Infer the position type from a callsign suffix.
     *
     * Maps: _ATIS→1, _DEL→2, _GND→3, _TWR→4, _APP→5,
     *       _CTR→6, _FSS→8. Falls back to TYPE_TOWER.
     */
    public static function inferTypeFromCallsign(string $callsign): int
    {
        $suffix = strtoupper(Arr::last(explode('_', $callsign)));

        return match ($suffix) {
            'ATIS' => self::TYPE_ATIS,
            'DEL', 'DELIVERY' => self::TYPE_DELIVERY,
            'GND', 'GROUND' => self::TYPE_GROUND,
            'TWR', 'TOWER' => self::TYPE_TOWER,
            'APP', 'APPROACH' => self::TYPE_APPROACH,
            'CTR' => self::TYPE_ENROUTE,
            'FSS' => self::TYPE_FSS,
            default => self::TYPE_TOWER,
        };
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
