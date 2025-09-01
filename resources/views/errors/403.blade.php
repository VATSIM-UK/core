@extends('layout', ['shellOnly' => true])

@section('content')
    <h1>403 - Forbidden</h1>
    <h4>Error Detail</h4>
    <pre>{{ $exception->getMessage() }}</pre>
    <p>If you require support with this issue, please contact <a href="mailto:technology-support@vatsim.uk">technology-support@vatsim.uk</a>.</p>
@stop
