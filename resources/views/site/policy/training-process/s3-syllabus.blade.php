@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    S3 Syllabus and Lesson Plans
                </div>
                <div class="panel-body">
                    @include("site.policy.training-process.s3-syllabus-body")
                </div>
            </div>
        </div>
    </div>

@endsection
