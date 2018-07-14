@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col xs-12">
            <div class="box box-warning">
                <div class="box-header">
                    <div class="box-title">Visiting Controllers Monitoring</div>
                    <div class="box-body">
                        <span>Date Range:</span> {{ $startDate }} -> {{ $endDate }}</div>

                    {!! link_to_route('visiting.admin.hours.index', 'January 2018',
                            [\Carbon\Carbon::parse('first day of january')->toDateString(), \Carbon\Carbon::parse('last day of january')->toDateString()]) !!}
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-body">
                    <div align="center">

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
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    @parent
    <script src='/AdminLTE/js/plugins/datatables/jquery.dataTables.min.js'></script>
    <script src='/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js'></script>
@stop