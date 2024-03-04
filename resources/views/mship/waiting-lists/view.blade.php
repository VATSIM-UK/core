@extends('layout')

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading">
                <i class="fa fa-list"></i> Waiting List Details
            </div>
            <div class="panel-body">
                {{ $list->name }}
                <p>Due to GCAP implementation this information is currently unavailable.
                    We hope to be able to restore this functionality soon.</p>
                {{-- <table class="table">
                    <tr>
                        <th>Name</th>
                        <td>{{$list->name}}</td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>{{ $list->formatted_department }}</td>
                    </tr>
                    @if($list->pivot->position)
                    <tr>
                        <th>Your Position</th>
                        <td>{{$list->pivot->position}}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Eligible</th>
                        <td>@if ($list->pivot->eligible)
                            {!! HTML::img("tick_mark_circle", "png", 20) !!}
                            @else
                            {!! HTML::img("cross_mark_circle", "png", 20) !!}
                            @endif</td>
                    </tr>
                </table> --}}
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
                        <tr>
                            <td colspan="2" class="text-center">{{$list->isATCList() ? ' and ': null}}
                                <strong>{{$list->flags_check}}</strong> of the following:
                            </td>
                        </tr>
                        @foreach($list->pivot->flags as $flag)
                        <tr>
                            <td>{{$flag->name}} ({{$flag->position_group_id ? 'Automatic': 'Manual'}})</td>
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
@endsection
