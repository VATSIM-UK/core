<?php

    namespace App\Models\Airport;

    use App\Models\Airport;
    use Illuminate\Database\Eloquent\Model;

    class Runway extends Model
    {
        protected $table = 'airport_runways';
        protected $fillable = [
            'ident',
            'heading',
            'width',
            'length',
            'surface_type',
        ];

        const SURFACE_TYPE_ASPHALT = 1;
        const SURFACE_TYPE_GRASS = 2;
        const SURFACE_TYPE_CONCRETE = 3;
        const SURFACE_TYPE_SAND = 4;
        const SURFACE_TYPE_GRE = 5;

        public function airport()
        {
            return $this->belongsTo(Airport::class);
        }

        public function procedures()
        {
            return $this->hasMany(Procedure::class);
        }

        public function getSurfaceTypeAttribute($type)
        {
            switch ($type) {
                case self::SURFACE_TYPE_ASPHALT:
                    return "Asphalt";
                case self::SURFACE_TYPE_GRASS:
                    return "Grass";
                case self::SURFACE_TYPE_CONCRETE:
                    return "Concrete";
                case self::SURFACE_TYPE_SAND:
                    return "Sand";
                case self::SURFACE_TYPE_GRE:
                    return "Graded/Rolled Earth";
                default:
                    return "Unknown";
            }
        }
    }
