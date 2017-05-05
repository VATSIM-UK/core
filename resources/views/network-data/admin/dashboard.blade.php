@extends('adm.layout')

@section('content')
        <!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-teal">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "atc_sessions_total", 0) }}
                </h3>
                <p>
                    <small>ATC Sessions (this year)</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "atc_sessions_hours", 0) }}
                </h3>
                <p>
                    <small>ATC Hours (this year)</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-orange">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "atc_uptime", 0) }}
                </h3>
                <p>
                    <small>ATC Uptime</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-blue">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "pilot_sessions_total", 0) }}
                </h3>
                <p>
                    <small>Pilot Sessions (this year)</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "pilot_sessions_hours", 0) }}
                </h3>
                <p>
                    <small>Pilot Hours (this year)</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="#" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3>
                    {{ array_get($statisticsRaw, "unknown", 0) }}
                </h3>
                <p>
                    <small>Unknown</small>
                </p>
            </div>
            <div class="icon">
                <i class="fa fa-check"></i>
            </div>
            <a href="#" class="small-box-footer">
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