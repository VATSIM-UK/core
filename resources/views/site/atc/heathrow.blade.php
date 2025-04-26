@extends('layout')

@section('content')
    <div class="row">

        <div class="col-md-9">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-pencil"></i> &thinsp;Heathrow Endorsements
                </div>
                <div class="panel-body">
                    @include("site.atc.heathrow-content")
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-question-sign"></i> &thinsp; FAQs
                </div>
                <div class="panel-body">
                    @include("site.atc.heathrow-faqs")
                </div>
            </div>
        </div>
    </div>
@stop
