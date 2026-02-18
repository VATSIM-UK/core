@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-book"></i> &thinsp; Policy Centre</div>
                <div class="panel-body">
                    <p>
                        This page brings together all VATSIM UK policy documents and training process syllabuses.
                    </p>
                    <p>
                        Choose a section below to view the full policy document.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-list-alt"></i> &thinsp; Core Policies</div>
                <div class="panel-body">
                    <ul>
                        <li><a href="{{ route('site.policy.division') }}">Division Policy</a></li>
                        <li><a href="{{ route('site.policy.atc-training') }}">ATC Training Policy</a></li>
                        <li><a href="{{ route('site.policy.visiting-and-transferring') }}">Visiting &amp; Transferring Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-lock"></i> &thinsp; Protecting You</div>
                <div class="panel-body">
                    <ul>
                        <li><a href="{{ route('site.policy.terms') }}">Terms &amp; Conditions</a></li>
                        <li><a href="{{ route('site.policy.privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('site.policy.data-protection') }}">Data Protection Policy</a></li>
                        <li><a href="{{ route('site.policy.branding') }}">Branding Guidelines</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-education"></i> &thinsp; ATC Training Process</div>
                <div class="panel-body">
                    <ul>
                        <li><a href="{{ route('site.policy.training.s1-syllabus') }}">S1 Syllabus and Lesson Plans</a></li>
                        <li><a href="{{ route('site.policy.training.s2-syllabus') }}">S2 Syllabus and Lesson Plans</a></li>
                        <li><a href="{{ route('site.policy.training.s3-syllabus') }}">S3 Syllabus and Lesson Plans</a></li>
                        <li><a href="{{ route('site.policy.training.c1-syllabus') }}">C1 Syllabus and Lesson Plans</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

@stop
