<?php

namespace App\Filament\Admin\Helpers\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

abstract class BaseListRecordsPage extends ListRecords
{
    protected function makeTable(): Table
    {
        return parent::makeTable()->paginationPageOptions([5, 10, 25, 50]);
    }
}
