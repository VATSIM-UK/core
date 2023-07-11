<?php

namespace App\Filament\Helpers\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class BaseEditRecordPage extends EditRecord
{
    use ChecksForGatedAttributes;
}
