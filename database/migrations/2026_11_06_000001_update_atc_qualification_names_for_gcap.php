<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAMES = [
        'S1' => 'Developing Controller',
        'S2' => 'Aerodrome Controller',
        'S3' => 'Terminal Controller',
        'C1' => 'Enroute Controller',
        'C3' => 'Senior Controller',
    ];

    public function up(): void
    {
        foreach (self::NAMES as $code => $name) {
            DB::table('mship_qualification')
                ->where('code', $code)
                ->where('type', 'atc')
                ->update(['name_long' => $name]);
        }
    }

    public function down(): void
    {
        foreach ([
            'S1' => 'Student 1',
            'S2' => 'Student 2',
            'S3' => 'Student 3',
            'C1' => 'Controller 1',
            'C3' => 'Senior Controller',
        ] as $code => $name) {
            DB::table('mship_qualification')
                ->where('code', $code)
                ->where('type', 'atc')
                ->update(['name_long' => $name]);
        }
    }
};
