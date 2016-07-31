@extends('layout')

@section('content')
    <div class="col-md-8 col-md-offset-2">
        {!! HTML::panelOpen("Login Alternative", ["type" => "fa", "key" => "unlock-alt"]) !!}
            <div class="row">
                <div class="col-md-12">
                    <p>
                        The VATSIM Certificate server is currently offline.  To allow our members to continue to access our services, anyone with a <strong>secondary password</strong> may now login.
                    </p>

                    <div class="col-md-8 col-md-offset-2">
                        {!! Form::horizontal(["route" => "mship.auth.loginAlternative.post", "method" => "POST"]) !!}

                            {!! ControlGroup::generate(
                                Form::label('cid', 'Account CID'),
                                Form::text('cid')
                            ) !!}

                            {!! ControlGroup::generate(
                                Form::label('password', 'Secondary Password'),
                                Form::password('password')
                            ) !!}

                            {!! ControlGroup::withContents(
                                Form::submit("Login")
                            )->withAttributes(["class" => "text-center"]) !!}

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        {!! HTML::panelClose() !!}
    </div>
@stop