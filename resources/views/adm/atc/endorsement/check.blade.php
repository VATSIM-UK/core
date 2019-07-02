@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Endorsement Status: <b>{{$account->full_name}}</b> for {{ $endorsement->name }}
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    @if ($endorsement->conditionsMetForUser($account))
                        <div class="alert alert-success" role="alert">
                            <strong>The user meets all criteria for this endorsement!</strong>
                        </div>
                    @else
                        <div class="alert alert-danger" role="alert">
                            <strong>The user has outstanding criterion for this endorsement</strong>
                        </div>
                    @endif
                    <table class="table">
                        <thead>
                        <th>Valid Positions</th>
                        <th>Requirement</th>
                        <th>Progress</th>
                        <th>Status</th>
                        </thead>
                        <tbody>
                        @foreach($endorsement->conditions as $condition)
                            <tr>
                                <td>{{implode(", ", $condition->human_positions)}}</td>
                                <td>
                                    {!! $condition->getHumanDescriptionAttribute(true) !!}
                                </td>
                                <td>
                                    @forelse ($condition->positionProgress($account) as $icao => $hours)
                                        {{$icao}} ({{ is_int($hours) ? '' : '~' }}{{round($hours,1)}} hours)</br>
                                    @empty
                                        None
                                    @endforelse
                                </td>
                                <td>
                                    @if ($condition->isMetForUser($account))
                                        {!! HTML::img("tick_mark_circle", "png", 35, 47) !!}
                                    @else
                                        {!! HTML::img("cross_mark_circle", "png", 35, 47) !!}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@endsection
