<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgSheetField extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'prog_sheet_fields';

    protected $primaryKey = 'field_id';

    public $timestamps = false;

    protected $guarded = [];

    public function progSheet(): BelongsTo
    {
        return $this->belongsTo(ProgSheet::class, 'prog_sheet_id', 'prog_sheet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgSheetCategory::class, 'catId', 'catId');
    }
}
