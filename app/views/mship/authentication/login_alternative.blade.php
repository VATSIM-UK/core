@extends('layout')

@section('title')
    Alternative Login
@stop

@section('content')
    <p>
        The VATSIM Certificate server is currently offline.  To allow our members to continue to access our services, anyone with a <strong>secondary password</strong> may now login.
    </p>

    <div class="row">
        <div class="col-md-7 col-md-offset-2">
            <form class="form-horizontal" method="POST" action="{{ URL::route("mship.auth.loginAlternative") }}">
                <div class="form-group">
                    <label class="col-sm-5 control-label" for="cid">Account CID</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="text" id="cid" name="cid" placeholder="Account CID">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-5 control-label" for="password">Secondary Password</label>
                    <div class="col-sm-7">
                        <input class="form-control" type="password" id="extra_password" name="password" placeholder="Secondary Password">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-5 col-sm-7">
                        <button type="submit" class="btn btn-default" name="processoverride" value="override">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop