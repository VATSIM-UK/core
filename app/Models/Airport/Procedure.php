<?php

    namespace App\Models\Airport;

    use App\Models\Airport;
    use Illuminate\Database\Eloquent\Model;

    class Procedure extends Model
    {
        protected $table = 'airport_procedures';
        protected $fillable = [
            'type',
            'ident',
            'initial_fix',
            'initial_altitude',
            'final_altitude',
            'remarks',
        ];

        const TYPE_SID = 1;
        const TYPE_STAR = 2;

        public function scopeWhereSID($query)
        {
            return $query->where('type', self::TYPE_SID);
        }

        public function scopeWhereSTAR($query)
        {
            return $query->where('type', self::TYPE_STAR);
        }

        public function airport()
        {
            return $this->belongsTo(Airport::class);
        }

        public function runway()
        {
            return $this->belongsTo(Runway::class);
        }

        public function getProcedureTypeAttribute($type)
        {
            switch ($type) {
                case self::TYPE_SID:
                    return "SID";
                case self::TYPE_STAR:
                    return "STAR";
                default:
                    return "";
            }
        }
    }
