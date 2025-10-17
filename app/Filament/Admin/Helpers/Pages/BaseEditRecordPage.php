<?php

namespace App\Filament\Admin\Helpers\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class BaseEditRecordPage extends EditRecord
{
    use ChecksForGatedAttributes;
}
