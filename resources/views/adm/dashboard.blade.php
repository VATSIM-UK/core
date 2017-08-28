@extends('adm.layout')

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-fuchsia">
            <div class="inner">
                <h3>
                    {{ number_format(array_get($statistics, "members_total", 0)) }}
                </h3>
                <p>
                    <small>Total Members</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-person-stalker"></i>
            </div>
            <a href="{{ URL::route("adm.mship.account.index", ["scope" => "all"]) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">

        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>
                    {{ number_format(array_get($statistics, "members_active", 0)) }}
                </h3>
                <p>
                    <small>Active Members</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ URL::route("adm.mship.account.index", ["scope" => "active"]) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>
                    {{ number_format(array_get($statistics, "members_division", 0)) }}
                </h3>
                <p>
                    <small>Division Members</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-home"></i>
            </div>
            <a href="{{ URL::route("adm.mship.account.index", ["scope" => "division"]) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>
                    {{ number_format(array_get($statistics, "members_nondivision", 0)) }}
                </h3>
                <p>
                    <small>Non-Div Members</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-earth"></i>
            </div>
            <a href="{{ URL::route("adm.mship.account.index", ["scope" => "nondivision"]) }}" class="small-box-footer">
                More info <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <div class="small-box bg-maroon">
            <div class="inner">
                <h3>
                    {{ number_format(array_get($statistics, "members_pending_update", 0)) }}
                </h3>
                <p>
                    <small>Pending Cert Updates</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-loop"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
            </a>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-2 col-xs-5">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>
                    {{ number_format(array_get($statistics, "members_qualifications", 0)) }}
                </h3>
                <p>
                    <small>Qualifications</small>
                </p>
            </div>
            <div class="icon">
                <i class="ion ion-trophy"></i>
            </div>
            <a href="#" class="small-box-footer">
                &nbsp;
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
                <li class="pull-left header"><i class="fa fa-inbox"></i> Membership Statistics (Last 90 days)</li>
            </ul>
            <div id="membership-statistics-chart" style="position: relative; height: 300px;"></div>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop

@section('scripts')
@parent
<script language="javascript" type="text/javascript">
    new Morris.Area({
        element: 'membership-statistics-chart',
        resize: true,
        data: [
            @foreach($membershipStats as $date => $counts)
                {
                    date: '{{ $date }}',
                    members_current: {{ $counts['members.current'] }},
                    division_current: {{ $counts['members.division.current'] }}
                },
            @endforeach
        ],
        xkey: 'date',
        xLabels: 'week',
        ykeys: [ 'members_current', 'division_current'],
        labels: [ 'Members (Current)', 'Division (Current)' ],
        lineColors: [ '#BC8D3C', '#3c8dbc' ],
        hideHover: 'auto',
        fillOpacity: 0.3,
        behaveLikeLine: true,
    });
</script>
@stop
