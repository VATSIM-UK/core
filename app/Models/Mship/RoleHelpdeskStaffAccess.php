<?php

declare(strict_types=1);

namespace App\Models\Mship;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleHelpdeskStaffAccess extends Model
{
    protected $table = 'mship_role_helpdesk_staff_access';

    protected $fillable = [
        'setting_id',
        'helpdesk_department_id',
        'helpdesk_role_id',
    ];

    protected $casts = [
        'helpdesk_department_id' => 'integer',
        'helpdesk_role_id' => 'integer',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(RoleHelpdeskStaffSetting::class, 'setting_id');
    }
}
