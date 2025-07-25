@extends('layout')

@section('content')

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i> &thinsp; VATGlasses
                </div>
                <div class="panel-body">
                @include("site.operations.markdown.vatglasses")                    
                </div>
            </div>
        </div>
        
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-map-marker" aria-hidden="true"></i> &thinsp; UK Area Sectors
                </div>
                <div class="panel-body">
                @include("site.operations.markdown.ukareasectors")
                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-departing-ifr">
                    <div class="panel-heading">
                        <i class="fa fa-plane-departure" aria-hidden="true"></i> &thinsp; I am <strong>departing</strong> from a UK airfield...
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-departing-ifr" class="panel-collapse collapse panel-body">
                @include("site.operations.markdown.departinguk")
                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-arriving-transiting">
                    <div class="panel-heading">
                        <i class="fa fa-plane-arrival" aria-hidden="true"></i> &thinsp; I am <strong>arriving</strong> at a UK airfield… / <i class="fa fa-plane" aria-hidden="true"></i> I am <strong>transiting</strong> through UK airspace…
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-arriving-transiting" class="panel-collapse collapse panel-body">
                @include("site.operations.markdown.arrivinguk")
                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-vfr">
                    <div class="panel-heading">
                        <i class="fa fa-binoculars" aria-hidden="true"></i> &thinsp; I am flying <strong>VFR</strong> within UK airspace…
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-vfr" class="panel-collapse collapse panel-body">
                @include("site.operations.markdown.vfr")
                </div>
            </div>
        </div>

    </div>

    <div class="row equal">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <a class="panel-heading-link" role="button" data-toggle="collapse" href="#sectors-military">
                    <div class="panel-heading">
                        <i class="fa fa-fighter-jet" aria-hidden="true"></i> &thinsp; I am operating a <strong>military</strong> flight within UK mainland airspace…
                        <i class="pull-right fa fa-arrow-down" aria-hidden="true"></i>
                    </div>
                </a>
                <div id="sectors-military" class="panel-collapse collapse panel-body">
                @include("site.operations.markdown.military")
                </div>
            </div>
        </div>

    </div>

@stop