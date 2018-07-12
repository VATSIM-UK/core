@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col xs-12">
            <div class="box box-warning">
                <div class="box-header">
                    <div class="box-title">Visiting Controllers Monitoring</div>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-body">
                    <div align="center">

                    </div>
                    <table id="visit-transfer-accounts" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="col-md-1">CID</th>
                            <th class="col-md-2">First Name / Last Name</th>
                            <th class="col-md-1">ATC Rating</th>
                            <th class="col-md-1">Region / Division</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($accounts as $account)
                                <tr>
                                    <td>{{ $account->id  }}</td>
                                    <td>{{ $account->name }}</td>
                                    <td>{{ $account->qualificationAtc }}</td>
                                    <td>{{ $account->primaryState->pivot->region }} / {{ $account->primaryState->pivot->division }} </td>
                                </tr>
                            @endforeach
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