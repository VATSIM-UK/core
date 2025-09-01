@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        All Facilities
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <div class="btn-toolbar">
                        <div class="btn-group pull-right">
                            <a href="{{ route('adm.visiting.facility.create') }}" class="btn btn-success">Create Facility</a>
                        </div>
                    </div>
                    <span class="clearfix">&nbsp;</span>
                    <table id="facilities" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="col-md-1">ID #</th>
                            <th>Name</th>
                            <th class="col-md-1" style="text-align: center;">Transfer/Visit</th>
                            <th class="col-md-1" style="text-align: center;">Training Required</th>
                            <th class="col-md-1" style="text-align: center;">Training Dept</th>
                            <th class="col-md-1" style="text-align: center;">Statement</th>
                            <th class="col-md-1" style="text-align: center;">Reference</th>
                            <th class="col-md-1" style="text-align: center;">Checks</th>
                            <th class="col-md-1" style="text-align: center;">Auto</th>
                            <th class="col-md-1" style="text-align: center;">Open</th>
                            <th class="col-md-1" style="text-align: center;">Visibility</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($facilities as $f)
                            <tr>
                                <td align="center"><a href="{{ route('adm.visiting.facility.update', [$f->id]) }}">{{ $f->id }}</a></td>
                                <td>{{ $f->name }}</td>
                                <td class="text-center">
                                    @if($f->can_visit && $f->can_transfer)
                                        <span class="label label-success">VISIT & TRANSFER</span>
                                    @elseif($f->can_visit)
                                        <span class="label label-warning">VISIT ONLY</span>
                                    @elseif($f->can_transfer)
                                        <span class="label label-warning">TRANSFER ONLY</span>
                                    @else
                                        <span class="label label-danger">NEITHER</span>
                                    @endif
                                </td>
                                <td align="center">
                                    @if($f->training_required)
                                        <span class="label label-success">YES</span>
                                        <span class="label label-info">{!! ( $f->training_spaces === null ? "&infin;" : $f->training_spaces) . " available"  !!}</span>
                                    @else
                                        <span class="label label-danger">NO</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ strtoupper($f->training_team) }}
                                </td>
                                <td align="center">
                                    @if($f->stage_statement_enabled)
                                        <span class="label label-success">YES</span>
                                    @else
                                        <span class="label label-danger">NO</span>
                                    @endif
                                </td>
                                <td align="center">
                                    @if($f->stage_reference_enabled)
                                        <span class="label label-success">YES</span>
                                        <span class="label label-info">{{ $f->stage_reference_quantity . " required" }}</span>
                                    @else
                                        <span class="label label-danger">NO</span>
                                    @endif
                                </td>
                                <td align="center">
                                    @if($f->stage_checks)
                                        <span class="label label-success">YES</span>
                                    @else
                                        <span class="label label-danger">NO</span>
                                    @endif
                                </td>
                                <td align="center">
                                    @if($f->auto_acceptance)
                                        <span class="label label-success">YES</span>
                                    @else
                                        <span class="label label-danger">NO</span>
                                    @endif
                                </td>
                                <td align="center">
                                    @if($f->open)
                                        <span class="label label-success">YES</span>
                                    @else
                                        <span class="label label-danger">NO</span>
                                    @endif
                                </td>
                                <td align="center">
                                    @if($f->public)
                                        <span class="label label-success">PUBLIC</span>
                                    @else
                                        <span class="label label-danger">PRIVATE</span>
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
@stop
