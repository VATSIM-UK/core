@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            @include('components.html.panel_open', [
                'title' => 'Secondary Authentication',
                'icon' => ['type' => 'fa', 'key' => 'fa-key'],
            ])
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
                    <form action="{{ route('auth-secondary.post') }}" method="POST" class="form-horizontal">
                        @csrf
                    <input name="remember" type="hidden" value="true">

                    <div class='form-group'>
                        <label class="control-label col-sm-5">Account</label>
                        <div class="col-sm-4">
                            <p class="form-control-static">{{ Auth::guard('vatsim-sso')->user()->id }} || <a href='#' class="form-control-static" data-toggle="modal" data-target="#notYou">Not You?</a></p>
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
                    </form>
                </div>
            </div>
            @include('components.html.panel_close')
        </div>
        <div class="col-md-2">
            <div class="panel panel-uk-danger">
              <div class="panel-heading"><i class="fa fa-exclamation-circle"></i> Having issues?</div>
              <div class="panel-body">
                Use your primary email address (*****<b>{{"@" . explode("@", Auth::user()->email)[1]}}</b>) to send an email to
                <p><a href="mailto:web-support@vatsim.uk">web-support@vatsim.uk</a></p>
              </div>
            </div>
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

    <div class="modal fade" id="notYou" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Not You?</h4>
                </div>
                <div class="modal-body">
                    <p>Not {{ Auth::guard('vatsim-sso')->user()->name }}? Please click below to logout.</p>
                </div>
                <div class="modal-footer">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('logout') }}">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-danger" id="confirm">Logout</button>
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop
