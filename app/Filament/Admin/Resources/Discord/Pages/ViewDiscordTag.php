<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\Discord\DiscordTagResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewDiscordTag extends BaseViewRecordPage
{
    protected static string $resource = DiscordTagResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tag Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('key'),
                        TextEntry::make('title'),
                    ]),
                Section::make('Content')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('value')
                            ->markdown(),
                    ]),
            ]);
    }
}
