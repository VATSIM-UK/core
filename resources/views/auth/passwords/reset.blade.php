@extends('layout')

@section('content')
    <div class="panel panel-ukblue">
        <div class="panel-heading"> Reset Password</div>

        <div class="panel-body">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            
            <p>
                To reset your secondary password, complete the form below.
            </p>
            
            <form class="form-horizontal" role="form" method="POST" action="{{ route('password.request') }}">
                {{ csrf_field() }}

                <input type="hidden" name="token" value="{{ $token }}">
                @include('auth.passwords.partials._new')
                @include('auth.passwords.partials._submit')
            </form>
        </div>
    </div>
@endsection
