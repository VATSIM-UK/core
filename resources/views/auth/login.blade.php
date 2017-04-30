@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            {!! HTML::panelOpen("Secondary Authentication", ["type" => "fa", "key" => "fa-key"]) !!}
            <div class="row">
                <div class="col-md-12">
                    <p>
                        Please enter your second, VATSIM UK password below. You will be requested to enter this password in any of the following situations:
                    <ol style="margin-left: 25px;">
                        <li>On your initial login to our system (after previously signing out, or being away for a long period).</li>
                        <li>If performing <em>destructive</em> actions on your account (i.e. for confirmation).</li>
                        <li>If other people have also logged in from the same IP address.</li>
                        <li>If you are a staff member.</li>
                    </ol>
                    </p>
                    <p>
                        If you believe you are seeing this page too often, or erroneously, please contact the Web Services Team.
                    </p>
                </div>

                <div class="col-md-8 col-md-offset-2">
                    {!! Form::horizontal(["route" => 'auth-secondary.post', "method" => "POST"]) !!}

                    {!! ControlGroup::generate(
                        Form::label("password", "Secondary Password"),
                        Form::password("password"),
                        Form::help("<a href='#' onclick='javascript: checkResetPassword();'>Forgotten Password?</a>")
                    ) !!}

                    {!! ControlGroup::withContents(
                        Form::submit("Login")
                    )->withAttributes(["class" => "text-center"]) !!}

                    {!! Form::close() !!}
                </div>
            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>

    <div class="modal fade" id="resetConfirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Reset Secondary Password</h4>
                </div>
                <div class="modal-body">
                    <p>Once you click this button, your old secondary password will be gone forever.  We'll then start the password recovery process for you - are you sure you wish to continue?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm" onclick="javascript: window.location.href ='{{ URL::route("mship.security.forgotten") }}';">Confirm</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Dialog show event handler -->
    <script type="text/javascript">
        function checkResetPassword(){
            $('#resetConfirmModal').modal()
        }
    </script>
@stop
