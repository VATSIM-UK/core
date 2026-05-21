<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgSheet extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'prog_sheet_name';

    protected $primaryKey = 'prog_sheet_id';

    public $timestamps = false;

    protected $guarded = [];

    public function categories(): HasMany
    {
        return $this->hasMany(ProgSheetCategory::class, 'prog_sheet_id', 'prog_sheet_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ProgSheetField::class, 'prog_sheet_id', 'prog_sheet_id');
    }
}
