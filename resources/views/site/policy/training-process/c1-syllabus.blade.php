@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    C1 Syllabus and Lesson Plans
                </div>
                <div class="panel-body">
                    @include("site.policy.training-process.c1-syllabus-body")
                </div>
            </div>
        </div>
    </div>
@endsection