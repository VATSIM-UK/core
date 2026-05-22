<?php

namespace App\Models\Cts;

use App\Enums\FieldScore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSheet extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'report_sheet';

    public $timestamps = false;

    protected $casts = [
        'field_score' => FieldScore::class,
    ];

    protected $guarded = [];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'seshid', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'student_id', 'id');
    }

    public function progSheet(): BelongsTo
    {
        return $this->belongsTo(ProgSheet::class, 'prog_sheet_id', 'prog_sheet_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ProgSheetField::class, 'field_id', 'field_id');
    }
}
