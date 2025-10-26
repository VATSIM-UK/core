@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-exclamation"></i>S2 Syllabus and Lesson Plans
                </div>
                <div class="panel-body">
                    @include("site.training.s2-syllabus-body")
                </div>
            </div>
        </div>
    </div>
@endsection