@extends('layout')

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading">
                <i class="fa fa-list"></i> Waiting List Details
            </div>
            <div class="panel-body">
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <td>{{$list->name}}</td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>{{$list->department}}</td>
                    </tr>
                    <tr>
                        <th>Your Status</th>
                        <td>{{$list->pivot->current_status}}</td>
                    </tr>
                    @if($list->pivot->position)
                    <tr>
                        <th>Your Position</th>
                        <td>{{$list->pivot->position}}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Eligible</th>
                        <td>@if ($list->pivot->eligibility)
                            {!! HTML::img("tick_mark_circle", "png", 20) !!}
                            @else
                            {!! HTML::img("cross_mark_circle", "png", 20) !!}
                            @endif</td>
                    </tr>
                </table>
                @if(
                    $list->pivot->current_status == 'Active' &&
                    $list->pivot->pending_removal?->isPendingRemoval()
                )
                <div class="alert alert-danger">
                    <strong>Important: </strong> You do not currently meet the hour check criteria of 12 hours in the last 3 months. You will be automatically removed from this waiting list in <b>{{\Carbon\Carbon::parse(\Carbon\Carbon::now())->diffInDays($list->pivot->pending_removal->removal_date)}} days</b> if you continue to not meet the requirement.
                </div>
                @endif
            </div>
        </div>
    </div>
    @if($list->isATCList() || count($list->pivot->flags))
    <div class="col-lg-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading">
                <i class="fa fa-check-circle"></i> Eligibility Criteria
            </div>
            <div class="panel-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Check</th>
                            <th>Currently Met</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($list->isATCList())
                        <tr>
                            <td colspan="2" class="text-center">
                                The following hour check:
                            </td>
                        </tr>
                        <tr>
                            <td>Hour Check (Automatic)</td>
                            <td>
                                <x-boolean-indicator :value="$list->pivot->atc_hour_check" />
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="2" class="text-center">{{$list->isATCList() ? ' and ': null}}
                                <strong>{{$list->flags_check}}</strong> of the following:
                            </td>
                        </tr>
                        @foreach($list->pivot->flags as $flag)
                        <tr>
                            <td>{{$flag->name}} ({{$flag->endorsement_id ? 'Automatic': 'Manual'}})</td>
                            <td>
                                <x-boolean-indicator :value="$flag->pivot->value" />
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@if($list->isATCList() || count($automaticFlags))
<div class="alert alert-warning">
    <strong>Important: </strong> Automated eligibility flags are only calculated every 24 hours! If you have just completed
    a network session, the flags shown above may not be accurate.
</div>
<div class="row">
    @if($list->isATCList())
    <div class="col-lg-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading">
                <i class="fa fa-flag"></i> Hour Check
            </div>
            <div class="panel-body">
                <p>
                    Have at least <strong>12 hours</strong> on <strong>UK controller positions</strong> in the last <strong>3 months</strong>.
                </p>
                <small>NB: Only sessions with primary frequencies count (i.e. not mentoring)</small>
                <x-progress-indicator max="12" :value="$list->pivot->recentATCMinutes() / 60" :text="number_format($list->pivot->recentATCMinutes() / 60, 1, null, '') . ' / 12 hours'" />
            </div>
        </div>
    </div>
    @endif
    @foreach($automaticFlags as $flag)
    <div class="col-lg-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading">
                <i class="fa fa-flag"></i> {{$flag->name}}
            </div>
            <div class="panel-body">
                @include('mship.waiting-lists._flag_breakdown', ["flag" => $flag, "user" => $list->pivot->account])
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection