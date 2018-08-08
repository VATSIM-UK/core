<?php

    namespace App\Models\Airport;

    use App\Models\Airport;
    use Illuminate\Database\Eloquent\Model;

    class Navaid extends Model
    {
        protected $table = 'airport_navaids';
        protected $fillable = [
            'type',
            'name',
            'heading',
            'ident',
            'frequency',
            'frequency_band',
            'remarks',
        ];

        const FREQUENCY_BAND_MHZ = 1;
        const FREQUENCY_BAND_KHZ = 2;

        public function airport()
        {
            return $this->belongsTo(Airport::class);
        }

        public function getFrequencyBandAttribute($band)
        {
            switch ($band) {
                case self::FREQUENCY_BAND_MHZ:
                    return "MHz";
                case self::FREQUENCY_BAND_KHZ:
                    return "KHz";
                default:
                    return "";
            }
        }
    }
