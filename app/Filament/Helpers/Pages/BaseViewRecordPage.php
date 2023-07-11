<?php

namespace App\Filament\Helpers\Pages;

use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecordPage extends ViewRecord
{
    use ChecksForGatedAttributes;
}
