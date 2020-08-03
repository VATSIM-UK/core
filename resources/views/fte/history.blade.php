@extends ('layout')

@section('content')

    <div class="col-md-12">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="fa fa-clock"></i> &thinsp; History</div>
            <div class="panel-body">
                All of your completed Flight Training Exercises are listed below.<br><br>
                <table class="table table-bordered table-striped table-hover">
                    <tr>
                        <th>Date</th>
                        <th>Exercise Name</th>
                        <th>Landing Rate</th>
                        <th>Duration</th>
                        <th>Outcome</th>
                        <th>Details</th>
                    </tr>
                    @foreach($pireps as $pirep)
                        <tr>
                            <td>{{ $pirep->created_at->format('dS M Y') }}</td>
                            <td>{{ $pirep->bid->flight->name }}</td>
                            <td>{{ $pirep->landing_rate }}fpm</td>
                            <td>{{ $pirep->flight_time }}</td>
                            <td>
                                @if($pirep->passed === true)
                                    <i class="fa fa-check"></i>
                                @elseif($pirep->passed === false)
                                    <i class="fa fa-times"></i>
                                @else
                                    <i class="fa fa-spinner"></i>
                                @endif
                            </td>
                            <td><a href="{{ route('fte.history', $pirep) }}">View</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

@stop
