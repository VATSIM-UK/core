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
                <div class="row">
                    <div align="center">
                        {!! $templates->render() !!}
                    </div>
                </div>
                <table id="mship-accounts" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Section</th>
                            <th>Area</th>
                            <th>Action</th>
                            <th style="text-align: center;">Priority</th>
                            <th style="text-align: center;">Sec. Emails</th>
                            <th>Reply To</th>
                            <th style="text-align: center;">Enabled</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $t)
                        <tr>
                            <td> {!! link_to_route('adm.sys.postmaster.template.view', $t->postmaster_template_id, [$t->postmaster_template_id]) !!} </td>
                            <td>{{ $t->section }}</td>
                            <td>{{ $t->area }}</td>
                            <td>{{ $t->action }}</td>
                            <td align="center">
                                @if($t->priority == \Models\Sys\Postmaster\Template::PRIORITY_LOW)
                                    <span class="label label-default">Low</span>
                                @elseif($t->priority == \Models\Sys\Postmaster\Template::PRIORITY_MED)
                                    <span class="label label-primary">Normal</span>
                                @elseif($t->priority == \Models\Sys\Postmaster\Template::PRIORITY_HIGH)
                                    <span class="label label-warning">High</span>
                                @elseif($t->priority == \Models\Sys\Postmaster\Template::PRIORITY_NOW)
                                    <span class="label label-danger">Immediate</span>
                                @endif
                            </td>
                            <td align="center">
                                @if($t->secondary_emails)
                                    <span class="label label-success">Allowed</span>
                                @else
                                    <span class="label label-danger">Disallowed</span>
                                @endif
                            </td>
                            <td>{{ $t->reply_to }}</td>
                            <td align="center">
                                @if($t->enabled)
                                    <span class="label label-success">Enabled</span>
                                @else
                                    <span class="label label-danger">Disabled</span>
                                @endif
                            </td>
                            <td>{{ $t->updated_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="row">
                    <div align="center">
                        {!! $templates->render() !!}
                    </div>
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent

@stop