<?php

namespace App\Filament\Helpers\Pages;

use Illuminate\Database\Eloquent\Model;

interface DefinesGatedAttributes
{
    /**
     * Returns a dictionary of attributes which are visible based on passing a truth test.
     *
     * This function is later consumed to hide sensitive information from model view/edit pages
     *
     * The array keys are attributes (e.g. email), and the array values are booleans
     */
    public static function gatedAttributes(Model $record): array;
}
