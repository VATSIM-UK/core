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
                    {{ $members->links() }}
                </div>
                <table id="mship-accounts" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>
                                @if($sortBy == "account_id")
                                    @if($sortDir == "ASC")
                                        {{ link_to_route("adm.mship.account.index", "ID", ["account_id", "DESC"]) }}
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        {{ link_to_route("adm.mship.account.index", "ID", ["account_id", "ASC"]) }}
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    {{ link_to_route("adm.mship.account.index", "ID", ["account_id", "ASC"]) }}
                                @endif
                            </th>
                            <th>
                                @if($sortBy == "name_first")
                                    @if($sortDir == "ASC")
                                        {{ link_to_route("adm.mship.account.index", "First Name", ["name_first", "DESC"]) }}
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        {{ link_to_route("adm.mship.account.index", "First Name", ["name_first", "ASC"]) }}
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    {{ link_to_route("adm.mship.account.index", "First Name", ["name_first", "ASC"]) }}
                                @endif
                                &nbsp;/&nbsp;
                                @if($sortBy == "name_last")
                                    @if($sortDir == "ASC")
                                        {{ link_to_route('adm.mship.account.index', "Last Name", ["name_last", "DESC"]) }}
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        {{ link_to_route('adm.mship.account.index', "Last Name", ["name_last", "ASC"]) }}
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    {{ link_to_route('adm.mship.account.index', "Last Name", ["name_last", "ASC"]) }}
                                @endif
                            </th>
                            <th>E-Mail</th>
                            <th>ATC Rating</th>
                            <th>Pilot Rating</th>
                            <th>State</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                        <tr>
                            <td>{{ link_to_route('adm.mship.account.details', $m->account_id, [$m->account_id]) }}</td>
                            <td>{{ $m->name }}</td>
                            <td>{{ $_account->hasPermission("adm/mship/account/view/email") ? $m->primary_email : "[ No Permission ]" }}</td>
                            <td>{{ $m->qualification_atc }}</td>
                            <td>{{ $m->qualification_pilot }}</td>
                            <td>{{ $m->current_state }}</td>
                            <td>{{ $m->status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div align="center">
                    {{ $members->links() }}
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
{{ HTML::script('/assets/js/plugins/datatables/dataTables.bootstrap.js') }}
@stop