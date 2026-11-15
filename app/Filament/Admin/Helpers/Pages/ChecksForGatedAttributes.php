<?php

namespace App\Filament\Admin\Helpers\Pages;

trait ChecksForGatedAttributes
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! is_subclass_of(static::$resource, HasGatedAttributes::class)) {
            return $data;
        }

        foreach ($this->gatedAttributes() as $attribute => $allowed) {
            if (! $allowed) {
                unset($data[$attribute]);
            }
        }

        return $data;
    }
}
