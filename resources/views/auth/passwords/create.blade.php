@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading"> Secondary Password</div>
        <div class="panel-body">
            <p>
                To create your secondary password, complete the form below.
            </p>

            <div class="row">
                <div class="col-md-7 col-md-offset-2">
                    <form action="{{ route('password.create') }}" class="form-horizontal" method="POST">
                    @csrf
                    @include('auth.passwords.partials._new')
                    @include('auth.passwords.partials._submit')
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
