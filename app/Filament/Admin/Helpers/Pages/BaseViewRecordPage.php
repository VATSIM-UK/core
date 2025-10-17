<?php

namespace App\Filament\Admin\Helpers\Pages;

use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecordPage extends ViewRecord
{
    use ChecksForGatedAttributes;
}
