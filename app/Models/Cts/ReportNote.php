<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportNote extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $table = 'report_notes';

    public $timestamps = false;

    protected $guarded = [];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'seshid', 'id');
    }
}
