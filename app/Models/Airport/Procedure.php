<?php

namespace App\Models\Airport;

use App\Models\Airport;
    use Illuminate\Database\Eloquent\Model;

    class Procedure extends Model
    {
        protected $table = 'airport_procedures';
        protected $fillable = [
            'procedure_type',
            'ident',
            'initial_fix',
            'initial_altitude',
            'final_altitude',
            'remarks',
        ];

        const PROCEDURE_TYPE_SID = 1;
        const PROCEDURE_TYPE_STAR = 2;

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
                case self::PROCEDURE_TYPE_SID:
                    return 'SID';
                case self::PROCEDURE_TYPE_STAR:
                    return 'STAR';
                default:
                    return '';
            }
        }
    }
