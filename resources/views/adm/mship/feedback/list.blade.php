@extends('adm.layout')

@section('content')
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12">
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs pull-right">
                <li class="pull-left header"><i class="fa fa-inbox"></i>Latest Feedback Submissions</li>
            </ul>
            <table id="mship-accounts" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="col-md-1">
                            ID
                        </th>
                        <th class="col-md-3">
                              Subject of Feedback
                        </th>
                        <th>Facility</th>
                        <th>Date Submitted</th>
                        <th>Action Taken</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feedback as $f)
                    <tr>
                        <td>{!! link_to_route('adm.mship.feedback.view', $f->id, [$f->id]) !!}</td>
                        <td>{{ $f->account->real_name }}</td>
                        <td>{{ $f->isATC() ? "ATC" : "Pilot"  }}</td>
                        <td>{{ $f->created_at->format("d-m-Y H:i A") }}</td>
                        <td>
                          @if ($f->actioned_at)
                              {!! HTML::img("tick_mark_circle", "png", 35, 47) !!}
                          @else
                              {!! HTML::img("cross_mark_circle", "png", 35, 47) !!}
                          @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop
