@extends('adm.layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col xs-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <div class="box-title">Visiting Controllers Monitoring</div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p>Here, you can review the controlling hours completed by visiting members within a given
                                        date range. This will allow you to ensure that they are compliant with the rules
                                        stipulated in the Visiting & Transferring Policy. If the row is displayed in red, it
                                        means that the member has controlled more than 49% of their hours for that specific date
                                        range in the UK, rather than in their home division.</p>
                                </div>
                            </div>
                        {!! Form::open(['route' => 'visiting.admin.hours.index', 'method' => 'get', 'autocomplete' => 'off']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="startDate">Start Date:</label>
                                    <!-- $startDate datetimepicker here -->
                                </div>
                            </div>
                        </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="endDate">Finish Date:</label>
                                        <!-- $endDate datetimepicker here -->
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
                                <th class="col-md-2">Name</th>
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
    </section>
@stop

@section('scripts')
    @parent
    <script src='/AdminLTE/js/plugins/datatables/jquery.dataTables.min.js'></script>
    <script src='/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js'></script>
@stop