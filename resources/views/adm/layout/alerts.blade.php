@if(Session::has('error') OR isset($error))
    <div class="alert alert-danger" role="alert">
        <strong>Error!</strong> {{ Session::has('error') ? Session::pull("error") : $error }}
    </div>
@endif

@if(count($errors) > 0)
    <div class="alert alert-danger" role="alert">
        <strong>Error!</strong> There were some errors with your request:
        <ul>
            @foreach($errors->getMessages() as $e)
                <li>{{ $e[0] }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(Session::has('success') OR isset($success))
    <div class="alert alert-success" role="alert">
        <strong>Success!</strong> {{ Session::has('success') ? Session::pull("success") : $success }}
    </div>
@endif