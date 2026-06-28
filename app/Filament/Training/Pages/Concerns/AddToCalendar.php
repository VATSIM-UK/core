<?php

namespace App\Filament\Training\Pages\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Spatie\CalendarLinks\Link;

trait AddToCalendar
{
    /**
     * Build a Spatie CalendarLinks Link object for the given record.
     *
     * @param  mixed  $record  The table record (e.g. ExamBooking, Session)
     */
    abstract protected function buildCalendarLinkObject(mixed $record): Link;

    /**
     * Get the ICS download filename (without extension) for the given record.
     */
    abstract protected function getCalendarIcsFilename(mixed $record): string;

    /**
     * Build a calendar link URL for the given record and provider.
     *
     * @param  mixed  $record  The table record
     * @param  string  $provider  One of: google, yahoo, webOutlook, webOffice
     * @return string The calendar provider URL
     */
    protected function buildCalendarLink(mixed $record, string $provider): string
    {
        $link = $this->buildCalendarLinkObject($record);

        return match ($provider) {
            'google' => $link->google(),
            'yahoo' => $link->yahoo(),
            'webOutlook' => $link->webOutlook(),
            'webOffice' => $link->webOffice(),
        };
    }

    /**
     * Get an ActionGroup containing "Add to Calendar" dropdown actions
     */
    protected function getCalendarActionGroup(): ActionGroup
    {
        return ActionGroup::make([
            Action::make('addToGoogle')
                ->label('Google Calendar')
                ->icon('heroicon-m-calendar')
                ->url(fn (mixed $record): string => $this->buildCalendarLink($record, 'google'))
                ->openUrlInNewTab(),
            Action::make('addToYahoo')
                ->label('Yahoo Calendar')
                ->icon('heroicon-m-calendar')
                ->url(fn (mixed $record): string => $this->buildCalendarLink($record, 'yahoo'))
                ->openUrlInNewTab(),
            Action::make('addToOutlookWeb')
                ->label('Outlook Web')
                ->icon('heroicon-m-calendar')
                ->url(fn (mixed $record): string => $this->buildCalendarLink($record, 'webOutlook'))
                ->openUrlInNewTab(),
            Action::make('addToOutlookDesktop')
                ->label('Outlook Desktop')
                ->icon('heroicon-m-calendar')
                ->url(fn (mixed $record): string => $this->buildCalendarLink($record, 'webOffice'))
                ->openUrlInNewTab(),
            Action::make('downloadIcs')
                ->label('Apple/Outlook (ICS)')
                ->icon('heroicon-m-arrow-down-tray')
                ->action(function (mixed $record) {
                    $link = $this->buildCalendarLinkObject($record);
                    $icsContent = $link->ics([], ['format' => 'file']);
                    $filename = $this->getCalendarIcsFilename($record).'.ics';

                    return response()->streamDownload(function () use ($icsContent) {
                        echo $icsContent;
                    }, $filename, ['Content-Type' => 'text/calendar']);
                }),
        ])
            ->label('Add to Calendar')
            ->icon('heroicon-m-calendar-days')
            ->color('gray')
            ->iconButton();
    }
}
