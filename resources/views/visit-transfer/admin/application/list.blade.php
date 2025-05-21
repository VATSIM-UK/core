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
                        {!! $applications->links() !!}
                    </div>
                    <table id="applications" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="col-md-1">
                                @if($sortBy == "id")
                                    @if($sortDir == "ASC")
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'id', 'sort_dir' => 'DESC']) }}">ID</a>
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'id', 'sort_dir' => 'ASC']) }}">ID</a>
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'id', 'sort_dir' => 'ASC']) }}">ID</a>
                                @endif
                            </th>
                            <th class="col-md-1">
                                @if($sortBy == "account_id")
                                    @if($sortDir == "ASC")
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'account_id', 'sort_dir' => 'DESC']) }}">Applicant ID</a>
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'account_id', 'sort_dir' => 'ASC']) }}">Applicant ID</a>
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'account_id', 'sort_dir' => 'ASC']) }}">Applicant ID</a>
                                @endif
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type & Facility</th>
                            <th class="col-md-1 text-center">
                                @if($sortBy == "created_at")
                                    @if($sortDir == "ASC")
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'created_at', 'sort_dir' => 'DESC']) }}">Created</a>
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'created_at', 'sort_dir' => 'ASC']) }}">Created</a>
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'created_at', 'sort_dir' => 'ASC']) }}">Created</a>
                                @endif
                            </th>
                            <th class="col-md-1 text-center">
                                @if($sortBy == "created_at")
                                    @if($sortDir == "ASC")
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'updated_at', 'sort_dir' => 'DESC']) }}">Updated</a>
                                        <small><i class="ion ion-arrow-up-b"></i></small>
                                    @else
                                        <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'updated_at', 'sort_dir' => 'ASC']) }}">Updated</a>
                                        <small><i class="ion ion-arrow-down-b"></i></small>
                                    @endif
                                @else
                                    <a href="{{ route('adm.visiting.application.list', ['sort_by' => 'updated_at', 'sort_dir' => 'ASC']) }}">Updated</a>
                                @endif
                            </th>
                            <th class="col-md-1 text-center">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($applications as $a)
                            <tr>
                                <td><a href="{{ route('adm.visiting.application.view', [$a->id]) }}">{{ $a->public_id }}</a></td>
                                <td>{{ $a->account_id }}</td>
                                <td>{{ $a->account->name  }}</td>
                                <td>{{ $_account->can('use-permission', "adm/mship/account/email/view") ? $a->account->email : "[ No Permission ]" }}</td>
                                <td>{{ $a->type_string }} - {{ $a->facility_name }}</td>
                                <td class="text-center">
                                    {{ $a->created_at->diffForHumans() }}
                                </td>
                                <td class="text-center">
                                    {{ $a->updated_at->diffForHumans() }}
                                </td>
                                <td class="text-center">
                                    @include("visit-transfer.partials.application_status", ["application" => $a])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td align="center" colspan="8">There are no applications that match your criteria.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div align="center">
                        {!! $applications->links() !!}
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop
