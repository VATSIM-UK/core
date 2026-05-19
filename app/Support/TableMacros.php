<?php

namespace App\Support;

use Filament\Tables\Columns\TextColumn;

class TableMacros
{
    public static function register(): void
    {
        TextColumn::macro('searchableByName', function () {
            /** @var TextColumn $this */
            return $this->searchableByName();
        });
    }
}
