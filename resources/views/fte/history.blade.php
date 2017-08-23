@extends ('layout')

@section('content')

    <div class="col-md-12">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-time"></i> &thinsp; History</div>
            <div class="panel-body">
                All of your completed Flight Training Exercises are listed below.<br><br>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                    <tr>
                        <th width="col-md-1">Date</th>
                        <th width="col-md-1">Flight ID</th>
                        <th width="col-md-2">Flight Name</th>
                        <th width="col-md-3">Landing Rate</th>
                        <th width="col-md-3">Duration</th>
                        <th width="col-md-3">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>21st July 2017</td>
                        <td>VFR1</td>
                        <td>Road Trip</td>
                        <td>-120fpm</td>
                        <td>1hr 16m</td>
                        <td><a href="{{ route('fte.history', 1) }}">View</a></td>
                    </tr>
                    <tr>
                        <td>20th June 2017</td>
                        <td>VFR2</td>
                        <td>King of the Castle</td>
                        <td>-235fpm</td>
                        <td>1hr 46m</td>
                        <td><a href="#">View</a></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop
