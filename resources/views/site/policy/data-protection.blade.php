@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-exclamation"></i> &thinsp; Data Protection & Handling Policy
                </div>
                <div class="panel-body">
                    @include("site.policy.data-protection-body")
                </div>
            </div>
        </div>
    </div>
    
@endsection
