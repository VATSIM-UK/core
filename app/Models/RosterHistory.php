<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterHistory extends Model
{
    use HasFactory;

    protected $table = 'roster_history';

    protected $fillable = ['account_id', 'original_created_at', 'original_updated_at', 'removed_by', 'roster_update_id'];
}
