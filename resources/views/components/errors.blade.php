<div class="container-fluid">
    @if(Session::has('error') OR isset($error))
        <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> {!! Session::has('error') ? Session::pull("error") : $error !!}
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Error!</strong> There is something wrong with your request:
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(Session::has('success') OR isset($success))
        <div class="alert alert-success" role="alert">
            <strong>Success!</strong> {!! Session::has('success') ? Session::pull("success") : $success !!}
        </div>
    @endif

    @if(Auth::guard('web')->check() && !Request::is("mship/notification*") && Auth::user()->has_unread_notifications)
        <div class="alert alert-warning" role="alert">
            You currently have unread notifications. You can view them on the
            <a href="{{ route('mship.notification.list') }}">notifications page</a>.
        </div>
    @endif
</div>

