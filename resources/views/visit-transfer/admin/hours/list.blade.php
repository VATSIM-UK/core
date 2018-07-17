@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col xs-12">
            <div class="box box-warning">
                <div class="box-header">
                    <div class="box-title">Visiting Controllers Monitoring</div>
                    <div class="box-body">
                        <strong>Date Range:</strong> {{ $startDate->toFormattedDateString() }} -> {{ $endDate->toFormattedDateString() }}</div>
                    {!! Form::open(['route' => 'visiting.admin.hours.index', 'method' => 'get', 'autocomplete' => 'off']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="startDate">Finish Date:</label>
                                    <input type="text" class="form-control datepicker" name="endDate">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="startDate">Start Date:</label>
                                    <input type="text" class="form-control datepicker" name="startDate">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
                                </div>
                            </div>
                        </div>

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-body">
                    <div align="center">
                        {{ $accounts->render() }}
                    </div>
                    <table id="visit-transfer-accounts" class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="col-md-1">CID</th>
                            <th class="col-md-2">First Name / Last Name</th>
                            <th class="col-md-1">ATC Rating</th>
                            <th class="col-md-1">Region / Division</th>
                            <th class="col-md-2">Controlling Time / Controlling Time (UK)</th>
                        </tr>
                        </thead>
                        <tbody>
                            @each('visit-transfer.admin.hours._tableRow', $accounts, 'account')
                        </tbody>
                    </table>
                    <div align="center">
                        {{ $accounts->render() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    @parent
    <script src='/AdminLTE/js/plugins/datatables/jquery.dataTables.min.js'></script>
    <script src='/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js'></script>

    <script language="JavaScript" type="text/javascript">
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
        });
    </script>
@stop