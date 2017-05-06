@extends('adm.layout')

@section('content')
        <!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-teal">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "applications_total", 0) }}
                </h3>
                <p>
                    <small>Total Applications</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="{{ URL::route("visiting.admin.application.list") }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">

        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "applications_open", 0) }}
                </h3>
                <p>
                    <small>Open Applications</small>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="{{ URL::route("visiting.admin.application.list", ["open"]) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "applications_closed", 0) }}
                </h3>
                <p>
                    <small>Closed Applications</small>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-times"></i>
            </div>
            <a href="{{ URL::route("visiting.admin.application.list", ["closed"]) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-light-blue">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "references_pending", 0) }}
                </h3>
                <p>
                    <small>Pending References</small>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
            <a href="{{ URL::route("visiting.admin.reference.list") }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "references_approval", 0) }}
                </h3>
                <p>
                    <small>References Under Review</small>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-search"></i>
            </div>
            <a href="{{ URL::route("visiting.admin.reference.list", ['approval']) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "references_accepted", 0) }}
                </h3>
                <p>
                    <small>References Accepted</small>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="{{ URL::route("visiting.admin.reference.list", ['accepted']) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
</div><!-- /.row -->

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs pull-right">
                <li class="pull-left header"><i class="fa fa-inbox"></i>Visiting &amp; Transferring Statistics (Last 180 days)</li>
            </ul>
            <div id="visit-transfer-statistics-chart" style="position: relative; height: 300px;"></div>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop

@section('scripts')
    @parent
    <script language="javascript" type="text/javascript">
        new Morris.Area({
            element: 'visit-transfer-statistics-chart',
            resize: true,
            data: [
                    @foreach($statisticsGraph as $date => $counts)
                {
                    date: '{{ $date }}',
                    applications_total: {{ array_get($counts, 'applications.total', 0) }},
                    applications_accepted: {{ array_get($counts, 'applications.accepted', 0) }},
                    applications_rejected: {{ array_get($counts, 'applications.rejected', 0) }},
                    applications_new: {{ array_get($counts, 'applications.new', 0) }}
                },
                @endforeach
            ],
            xkey: 'date',
            xLabels: 'week',
            ykeys: [ "applications_total", "applications_accepted", "applications_rejected", "applications_new" ],
            labels: [ "Applications (Total)", "Applications (Accepted)", "Applications (Rejected)", "Applications (New)" ],
            lineColors: [ '#b700b8', '#ffd700', '#c0d800', '#bbbbbb' ],
            hideHover: 'auto',
            fillOpacity: 0.3,
            behaveLikeLine: true,
        });
    </script>
@stop