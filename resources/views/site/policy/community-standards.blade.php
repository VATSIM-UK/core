@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-exclamation"></i> &thinsp; Community Standards
                </div>
                <div class="panel-body">
                    @include("site.policy.community-standards-body")
                </div>
            </div>
        </div>
    </div>

@endsection
