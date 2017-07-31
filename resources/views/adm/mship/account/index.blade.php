@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header">
                <div class="box-title">Search Criteria</div>
            </div>
            <div class="box-body">

            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Search Results
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div align="center">
                    {!! $membersQuery->render() !!}
                </div>
                <table id="mship-accounts" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="col-md-1">
                                @if($sortBy == "id")
                                    @if($sortDir == "ASC")
                                        {!! link_to_route("adm.mship.account.index", "ID", ["sort_by" => "id", "sort_dir" => "DESC"]) !!}
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        {!! link_to_route("adm.mship.account.index", "ID", ["sort_by" => "id", "sort_dir" => "ASC"]) !!}
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    {!! link_to_route("adm.mship.account.index", "ID", ["sort_by" => "id", "sort_dir" => "ASC"]) !!}
                                @endif
                            </th>
                            <th class="col-md-3">
                                @if($sortBy == "name_first")
                                    @if($sortDir == "ASC")
                                        {!! link_to_route("adm.mship.account.index", "First Name", ["sort_by" => "name_first", "sort_dir" => "DESC"]) !!}
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        {!! link_to_route("adm.mship.account.index", "First Name", ["sort_by" => "name_first", "sort_dir" => "ASC"]) !!}
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    {!! link_to_route("adm.mship.account.index", "First Name", ["sort_by" => "name_first", "sort_dir" => "ASC"]) !!}
                                @endif
                                &nbsp;/&nbsp;
                                @if($sortBy == "name_last")
                                    @if($sortDir == "ASC")
                                        {!! link_to_route('adm.mship.account.index', "Last Name", ["sort_by" => "name_last", "sort_dir" => "DESC"]) !!}
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        {!! link_to_route('adm.mship.account.index', "Last Name", ["sort_by" => "name_last", "sort_dir" => "ASC"]) !!}
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    {!! link_to_route('adm.mship.account.index', "Last Name", ["sort_by" => "name_last", "sort_dir" => "ASC"]) !!}
                                @endif
                            </th>
                            <th>E-Mail</th>
                            <th>ATC Rating</th>
                            <th>ATC Training</th>
                            <th>Pilot Rating(s)</th>
                            <th>Pilot Training</th>
                            <th>Admin Rating</th>
                            <th>State</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                        <tr>
                            <td>{!! link_to_route('adm.mship.account.details', $m->id, [$m->id]) !!}</td>
                            <td>{{ $m->name }}</td>
                            <td>{{ $_account->hasPermission("adm/mship/account/email/view") ? $m->email : "[ No Permission ]" }}</td>
                            <td>{{ $m->qualification_atc }}</td>
                            <td>{{ $m->qualifications_atc_training->count() > 0 ? $m->qualifications_atc_training->first() : null }}</td>
                            <td>{{ $m->qualifications_pilot_string }}</td>
                            <td>{{ $m->qualifications_pilot_training->count() > 0 ? $m->qualifications_pilot_training->first() : null }}</td>
                            <td>{{ $m->qualifications_admin->count() > 0 ? $m->qualifications_admin->first() : null }}</td>
                            <td>
                                {{ $m->primary_state->name }}

                                @if($m->primary_state->pivot->region)
                                    ({{ $m->primary_state->pivot->region }}
                                    @if($m->primary_state->pivot->division)
                                        / {{ $m->primary_state->pivot->division }}
                                    @endif
                                    )
                                @endif
                            </td>


                            <td>{!! $m->status_string == "Active" ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">'.$m->status_string.'</span>' !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div align="center">
                    {!! $membersQuery->render() !!}
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
{!! HTML::script('/AdminLTE/js/plugins/datatables/jquery.dataTables.min.js') !!}
{!! HTML::script('/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js') !!}
@stop
