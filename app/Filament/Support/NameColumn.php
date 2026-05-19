<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\TextColumn;

class NameColumn extends TextColumn
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'name')->searchable(['name_first', 'name_last']);
    }
}
