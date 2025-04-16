@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading"> Secondary Password</div>
        <div class="panel-body">
            <p>To disable your secondary password, please enter your current password below.</p>

            <div class="row">
                <div class="col-md-7 col-md-offset-2">
                    <form action="{{ route('password.delete') }}" class="form-horizontal" method="POST">
                    @csrf
                    @include('auth.passwords.partials._old')
                    @include('auth.passwords.partials._submit')
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
