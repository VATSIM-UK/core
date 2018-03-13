@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Endorsement Status: {{$account->full_name}} for {{ $endorsement }}
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    @if (!$criteria->pluck('met')->contains(false))
                        <div class="alert alert-success" role="alert">
                            <strong>The user meets all crteria for this endorsement!</strong>
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
                            @foreach($criteria as $c)
                                <tr>
                                    <td>{{implode(", ", $c->requirements->required_airfields)}}</td>
                                    <td><strong>{{$c->requirements->required_hours}} hours</strong> within the last <strong>{{$c->requirements->hours_months}} months</strong> on <strong>any single airport</strong></td>
                                    <td>
                                        @foreach($c->hours as $key => $hour)
                                            {{$key}} ({{round($hour,1)}} hours)</br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if ($c->met)
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

