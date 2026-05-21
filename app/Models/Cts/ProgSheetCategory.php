<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgSheetCategory extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'prog_sheet_categories';

    protected $primaryKey = 'catId';

    public $timestamps = false;

    protected $guarded = [];

    public function progSheet(): BelongsTo
    {
        return $this->belongsTo(ProgSheet::class, 'prog_sheet_id', 'prog_sheet_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ProgSheetField::class, 'catId', 'catId');
    }
}
