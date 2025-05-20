@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Edit Member's Flight</h3>
                </div>
                <div class="box-body">
                    <form method="POST" action="{{ route('adm.smartcars.flights.update', $pirep) }}">
                        @csrf
                        @method('PUT')

                    <label>Pass/Fail<i class="fa fa-asterisk text-danger"></i></label>
                    <div class="radio" style="margin-top: 0;">
                        <label>
                            <input type="radio" name="passed" value="1" {{ $pirep->passed ? 'checked' : '' }}> Passed {{ $pirep->passed ? '- Current' : '' }}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="passed" value="0" {{ !$pirep->passed ? 'checked' : '' }}> Failed {{ !$pirep->passed ? '- Current' : '' }}
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Change (Visible to User)<i class="fa fa-asterisk text-danger"></i></label>
                        <input type="text" id="reason" name="reason" class="form-control"
                               value="{{ old('reason') ?: $pirep->pass_reason }}" required>
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.flights.index') }}">Cancel</a>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-ukblue" style="min-height: 500px;">
                <div class="panel-heading"><i class="fa fa-globe"></i> &thinsp; Map
                </div>
                <div class="panel-body">
                    <p><strong>LEGEND:</strong> <span style="color: #228B22;">Target</span>, <span style="color: #00008B;">Actual</span></p>
                    <div id="map" style="width: 100%; height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var poly;
        var map;
        var polyActual;

        function initMap() {
            google.maps.Polyline.prototype.getBounds = function() {
                var bounds = new google.maps.LatLngBounds();
                this.getPath().forEach(function(element,index){ bounds.extend(element); });
                return bounds;
            };

            map = new google.maps.Map(document.getElementById('map'));

            @include('fte.map.plot-criteria')

            @include('fte.map.mark-airports')

            map.setCenter(poly.getBounds().getCenter());
            map.setZoom(map.fitBounds(poly.getBounds()));

            @include('fte.map.plot-posreps')

        }
    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.jsapi') }}&callback=initMap">
    </script>
@endsection
