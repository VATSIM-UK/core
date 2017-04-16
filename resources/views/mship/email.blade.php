@extends('layout')

@section('content')

    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading">Email a Member</div>
            <div class="panel-body">
                <p>You may use this to email division, visiting and transferring members. You may not use this form to email other regional or international members, or inactive members.</p>
                {!! Form::open(['route' => ['mship.email.post'], 'class' => 'form-horizontal']) !!}
                <div class="form-group">
                    <label for="recipient" class="col-sm-4 control-label">Recipient</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="recipient" placeholder="Search by CID or Name">
                    </div>
                </div>
                <div class="form-group">
                    <label for="subject" class="col-sm-4 control-label">Subject</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="subject" placeholder="Subject">
                    </div>
                </div>
                <div class="form-group">
                    <label for="message" class="col-sm-4 control-label">Message</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" rows="3" id="message" placeholder="Enter your message here"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-4">
                        <button type="submit" class="btn btn-default" id="send">Send</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

@stop
