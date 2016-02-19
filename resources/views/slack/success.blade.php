@extends('layout')

@section('content')

    <div class="row">
        <div class="alert alert-success" role="alert">
            Registration Successful! Redirecting...
        </div>
    </div>

<script type="text/javascript">
setTimeout(function(){ window.location = "{{ route('mship.manage.dashboard') }}"; }, 4000);
</script>

@stop
