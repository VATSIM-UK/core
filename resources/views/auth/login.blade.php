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
                    <input name="remember" type="hidden" value="true">

                    <div class='form-group'>
                        <label class="control-label col-sm-5">Account</label>
                        <div class="col-sm-4">
                            <p class="form-control-static">{{ Auth::guard('vatsim-sso')->user()->id }}</p>
                        </div>
                    </div>

                    <div class='form-group'>
                        <label for="password" class="control-label col-sm-5">Secondary Password</label>
                        <div class="col-sm-4">
                            <input class="form-control" name="password" type="password" value="" id="password">
                        </div>
                    </div>

                    <div class='form-group'>
                        <div class="col-sm-4 col-sm-offset-5">
                            <a href='#' class="form-control-static" data-toggle="modal" data-target="#resetConfirmModal">Forgotten Password?</a>
                        </div>
                    </div>

                    <div class='form-group'>
                        <div class="col-sm-4 col-sm-offset-5">
                            <input class="btn btn-default" type="submit" value="Login">
                        </div>
                    </div>


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
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="confirm">Confirm</button>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop
