<?php

namespace App\Filament\Admin\Helpers\Pages;

use App\Filament\Admin\Helpers\Resources\DefinesGatedAttributes;

trait ChecksForGatedAttributes
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! is_subclass_of(static::$resource, DefinesGatedAttributes::class)) {
            return $data;
        }

        foreach (static::$resource::gatedAttributes($this->getRecord()) as $attribute => $allowed) {
            if (! $allowed) {
                unset($data[$attribute]);
            }
        }

        return $data;
    }
}
