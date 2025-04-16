@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading"> Secondary Password</div>
        <div class="panel-body">
            <p>
                To change your secondary password, complete the form below.
            </p>

            <div class="row">
                <div class="col-md-7 col-md-offset-2">
                    <form action="{{ route('password.change') }}" method="POST" class="form-horizontal">
                    @csrf
                    @include('auth.passwords.partials._old')
                    @include('auth.passwords.partials._new')
                    @include('auth.passwords.partials._submit')
                    </form>
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
@stop
