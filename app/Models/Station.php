<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Station extends Model
    {
        protected $table = 'stations';
        protected $fillable = [
            'callsign',
            'name',
            'frequency',
            'type',
        ];

        const TYPE_ATIS = 1;
        const TYPE_DELIVERY = 2;
        const TYPE_GROUND = 3;
        const TYPE_TOWER = 4;
        const TYPE_APPROACH = 5;
        const TYPE_ENROUTE = 6;
        const TYPE_TERMINAL = 7;
        const TYPE_FSS = 8;

        public function airports()
        {
            return $this->belongsToMany(Airport::class);
        }

        public function getTypeAttribute($type)
        {
            switch ($type) {
                case self::TYPE_ATIS:
                    return "ATIS";
                case self::TYPE_DELIVERY:
                    return "Delivery";
                case self::TYPE_GROUND:
                    return "Ground";
                case self::TYPE_TOWER:
                    return "Tower";
                case self::TYPE_APPROACH:
                    return "Approach/Radar";
                case self::TYPE_ENROUTE:
                    return "Enroute";
                case self::TYPE_TERMINAL:
                    return "Terminal Control";
                case self::TYPE_FSS:
                    return "Flight Service Stations";
                default:
                    return "Unknown";
            }
        }
    }
