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
                    </tr>
                </thead>
                <tbody>
                    @foreach($feedback as $f)
                    <tr>
                        <td>{!! link_to_route('adm.mship.feedback.view', $f->id, [$f->id]) !!}</td>
                        <td>{{ $f->account->name_first }} {{ $f->account->name_last }}</td>
                        <td>{{ $f->isATC() ? "ATC" : "Pilot"  }}</td>
                        <td>{{ $f->created_at->format("d-m-Y H:i A") }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop
