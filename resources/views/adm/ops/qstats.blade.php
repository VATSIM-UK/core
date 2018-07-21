@extends('adm.layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col xs-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <div class="box-title">Quarterly Stats</div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p>Here, you can generate statistics that are used for the quarterly report to VATEUR.</p>
                                </div>
                            </div>
                            {!! Form::open(['route' => 'adm.ops.qstats.generate', 'method' => 'post', 'autocomplete' => 'off']) !!}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label("quarter" ,"Quarter",['class' => 'control-label']),
                                        Form::select("quarter", ['01-01' => 'Q1', '04-01' => 'Q2', '07-01' => 'Q3', '10-01' => 'Q4'],'Q1', ['class' => 'form-control', 'id' => 'quarter']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label("year","Year",['class' => 'control-label']),
                                        Form::number("year", \Carbon\Carbon::now()->year, ['class' => 'form-control', 'id' => 'year']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        {!! Form::submit('Generate', ['class' => 'btn btn-primary']) !!}
                                    </div>
                                </div>
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop