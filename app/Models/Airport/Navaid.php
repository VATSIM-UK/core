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

        const TYPE_NDB = 1;
        const TYPE_VORDME = 2;
        const TYPE_DME = 3;
        const TYPE_ILS = 4;
        const TYPE_TACAN = 5;

        public function airport()
        {
            return $this->belongsTo(Airport::class);
        }

        public function getTypeAttribute($type)
        {
            switch ($type) {
                case self::TYPE_NDB:
                    return "NDB";
                case self::TYPE_VORDME:
                    return "VOR/DME";
                case self::TYPE_DME:
                    return "DME";
                case self::TYPE_ILS:
                    return "ILS";
                case self::TYPE_TACAN:
                    return "TACAN";
                default:
                    return "";
            }
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
