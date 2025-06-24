@extends('layout')

@section('content')

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-cog"></i> &thinsp; Bookings Calendar
                </div>
                <div class="panel-body">
                    <p>
                        The bookings calendar shows the availability of our controllers for bookings. You can navigate through the months using the links below.
                    </p>
                </div>
                <div class="panel-heading"><h2>{{ $date->format('F Y') }}</h2></div>
                <div class="panel-body">
                    <div style="margin-bottom: 15px;">
                        <p class="text-center">Use the links below to navigate through the months.</p>
                        <a href="{{ route('site.bookings.calendar', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">
                            ← Previous</a>
                            &nbsp;|&nbsp;
                        <a href="{{ route('site.bookings.calendar', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">
                            Next →</a>
                    </div>
                    <div class="table-responsive">
                        <table border="1" cellpadding="5" cellspacing="0">
                            <thead>
                                <tr>
                                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                    <th>{{ $day }}</th>
                                    
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($calendar as $week)
                                <tr>
                                    @foreach($week as $day)
                                    <td style="background-color: {{ $day->month !== $date->month ? '#eee' : '#fff' }}">
                                        {{ $day->day }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
        </div>

@stop