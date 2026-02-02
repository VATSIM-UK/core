<?php

namespace App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource\Pages;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewVisitTransferApplication extends ViewRecord
{
    protected static string $resource = VisitTransferApplicationResource::class;

    public function getTitle(): string
    {
        return 'View '.$this->record->account?->full_name.'\'s '.$this->record->type_string.' Application #'.$this->record->public_id;
    }

    protected function getHeaderActions(): array
    {
        $application = $this->record;

        return [
            Action::make('accept')
                ->label('Accept')
                ->color('success')
                ->modalHeading(fn ($record) => "Accept Application #{$record->public_id}")
                ->modalDescription('If you accept this application the applicant will be notified.')
                ->action(function ($record, array $data) {
                    $record->accept($data['staff_note']);
                })
                ->form([
                    Textarea::make('staff_note')
                        ->label('Staff Note (optional)'),
                ])->authorize(fn ($record) => auth()->user()->can('accept', $record)),

            Action::make('override_checks')
                ->label('Override Checks')
                ->color('warning')
                ->modalHeading(fn ($record) => 'Override Checks')
                ->action(function () use ($application) {
                    $application->check_outcome_90_day = true;
                    $application->check_outcome_50_hours = true;
                    $application->save();
                })
                ->requiresConfirmation()
                ->authorize(fn () => auth()->user()->can('overrideChecks', $application))
                ->successNotificationTitle('Checks overridden'),

            Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->modalHeading(fn ($record) => "Reject Application #{$record->public_id}")
                ->modalDescription('This action cannot be undone. If you reject this application the applicant will be notified.')
                ->action(fn ($record, array $data) => $record->reject($data['reason'], $data['staff_note']))
                ->form([
                    Select::make('reason')
                        ->label('Reason for Rejection (required)')
                        ->options([
                            'Non-compliant with Visiting & Transferring Policy' => 'Non-compliant with Visiting & Transferring Policy',
                            'Lack of Engagement' => 'Lack of Engagement',
                            'Incorrect Rating' => 'Incorrect Rating',
                            'other' => 'Other',
                        ])
                        ->reactive()
                        ->required(),
                    Textarea::make('other_reason') // Not sure this is correct, to be checked later
                        ->label('Reason for Rejection (required)')
                        ->required()
                        ->visible(fn ($get) => $get('reason') === 'other'),
                    Textarea::make('staff_note')
                        ->label('Staff Note (required)')
                        ->required(),
                ])->authorize(fn ($record) => auth()->user()->can('reject', $record)),

            Action::make('complete')
                ->label('Complete')
                ->color('primary')
                ->modalHeading(fn ($record) => "Mark Application #{$record->public_id} as Completed")
                ->modalDescription('This action cannot be undone. This will mark the application as completed.')
                ->action(fn ($record, array $data) => $record->complete($data['staff_note']))
                ->form([
                    Textarea::make('staff_note')
                        ->label('Staff Note (required)')
                        ->required(),
                ])->authorize(fn ($record) => auth()->user()->can('complete', $record)),

            Action::make('cancel')
                ->label('Cancel')
                ->color('danger')
                ->modalHeading(fn ($record) => "Cancel Application #{$record->public_id}")
                ->modalDescription('This action cannot be undone. This will cancel the application.')
                ->action(fn ($record, array $data) => $record->cancel($data['cancel_reason'], $data['staff_note']))
                ->form([
                    Textarea::make('cancel_reason')
                        ->label('Reason for Cancellation (required)')
                        ->required(),
                    Textarea::make('staff_note')
                        ->label('Staff Note (required)')
                        ->required(),
                ])->authorize(fn ($record) => auth()->user()->can('cancel', $record)),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {

        $application = $this->record;

        return $infolist->record($application)->schema([
            TextEntry::make('status')->label('Status')->formatStateUsing(fn ($state, $record) => $record->status_string)->badge()->color(fn ($record) => $record->status_color),
            Section::make('Application Content #'.$application->public_id)
                ->schema([
                    Split::make([
                        Section::make('Member Overview')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('account.full_name')->label('Applicant Name'),
                                    TextEntry::make('account.id')->label('Applicant ID'),
                                    TextEntry::make('atc_rating')->label('ATC Rating')->getStateUsing(fn ($record) => ($record->account?->qualification_atc?->name_long ?? 'Unknown ATC')),
                                    TextEntry::make('pilot_rating')->label('Pilot Rating')->getStateUsing(fn ($record) => ($record->account?->qualification_pilot?->name_long ?? 'Unknown Pilot')),
                                    TextEntry::make('type_string')->label('Facility Type'),
                                    TextEntry::make('facility.name')->label('Facility Name'),
                                    TextEntry::make('created_at')->label('Created At')->dateTime(),
                                    TextEntry::make('statement')->label('Statement')->columnSpan(2)->getStateUsing(fn ($record) => $record->statement ?? 'No statement provided'),
                                ]),
                            ]),
                        Section::make('Stages & Automated Checks')
                            ->schema([
                                Grid::make(4)->schema([
                                    IconEntry::make('training_required')->label('Training Required')->getStateUsing(fn ($record) => $record->training_required)->boolean(),
                                    IconEntry::make('statement_required')->label('Statement Required')->getStateUsing(fn ($record) => $record->statement_required)->boolean(),
                                    IconEntry::make('should_perform_checks')->label('Auto Check')->getStateUsing(fn ($record) => $record->should_perform_checks)->boolean(),
                                    IconEntry::make('will_auto_accept')->label('Auto Accept')->getStateUsing(fn ($record) => $record->will_auto_accept)->boolean(),

                                    IconEntry::make('check_outcome_90_day')->label('90 Days Check')->getStateUsing(function ($record) {
                                        return match ($record->check_outcome_90_day) {
                                            true => true,
                                            false => false,
                                            null => false,
                                        };
                                    })->boolean(),
                                    IconEntry::make('check_outcome_50_hours')->label('50 Hours Check')->getStateUsing(function ($record) {
                                        return match ($record->check_outcome_50_hours) {
                                            true => true,
                                            false => false,
                                            null => false,
                                        };
                                    })->boolean(),
                                ]),
                            ]),
                    ])->from('md'), // stacks on smaller screens

                    Split::make([
                        Section::make('Memberships')
                            ->description('Current and Past Memberships of the Applicant')
                            ->schema([
                                Grid::make(1)->schema(
                                    ($application->account?->statesHistory ?? collect())
                                        ->sortByDesc('pivot.start_at')
                                        ->map(function ($state) {
                                            $status = ($state->pivot->end_at) ? 'Expired' : 'Active - '.($state->is_permanent ? 'Permanent' : 'Temporary');

                                            return TextEntry::make("state_{$state->id}")
                                                ->label("{$state->name} ({$status})")
                                                ->getStateUsing(fn () => "Region: {$state->pivot->region}, Division: {$state->pivot->division}, Start: {$state->pivot->start_at?->toFormattedDateString()}".($state->pivot->end_at ? ', End: '.Carbon::parse($state->pivot->end_at)->toFormattedDateString() : ''));
                                        })->toArray()
                                ),
                            ]),
                        Section::make('Member Notes')
                            ->description('Check for any recent notes that may be relevant to this application')
                            ->schema([
                                Grid::make(1)->schema(
                                    ($application->account?->notes ?? collect())
                                        ->map(function ($note) {
                                            return TextEntry::make("note_{$note->id}")
                                                ->label("Note by {$note->writer?->full_name} on {$note->created_at->toFormattedDateString()}")
                                                ->getStateUsing(fn () => $note->content);
                                        })->toArray()
                                ),
                            ]),
                    ])->from('md'), // stacks on smaller screens

                    Section::make('Previous Applications')
                        ->description('Previous Visiting & Transferring Applications by this Member')
                        ->schema([
                            Grid::make(1)->schema(
                                ($application->account?->visitTransferApplications ?? collect())
                                    ->where('id', '!=', $application->id)
                                    ->sortByDesc('created_at')
                                    ->map(function ($oldapp) {
                                        return Grid::make(5)->schema([
                                            TextEntry::make("app_{$oldapp->id}_id")
                                                ->label('Application ID')
                                                ->getStateUsing(fn () => $oldapp->public_id ?? 'Unknown')
                                                ->url($oldapp->id ? route('filament.app.resources.visit-transfer.visit-transfer-applications.view', ['record' => $oldapp->id]) : null)
                                                ->color('primary'),
                                            TextEntry::make("app_{$oldapp->id}_type")
                                                ->label('Type')
                                                ->getStateUsing(fn () => $oldapp->type_string ?? 'Unknown'),
                                            TextEntry::make("app_{$oldapp->id}_facility")
                                                ->label('Facility')
                                                ->getStateUsing(fn () => $oldapp->facility?->name ?? 'Deleted Facility'),
                                            TextEntry::make("app_{$oldapp->id}_status")
                                                ->label('Status')
                                                ->getStateUsing(fn () => $oldapp->status_string ?? 'Unknown')
                                                ->badge()
                                                ->color(fn () => $oldapp->status_color ?? 'gray'),
                                            TextEntry::make("app_{$oldapp->id}_created")
                                                ->label('Created')
                                                ->getStateUsing(fn () => optional($oldapp->created_at)->toDayDateTimeString() ?? 'Unknown'),
                                        ]);
                                    })->toArray()
                            ),
                        ]),
                ]),
        ]);
    }
}
