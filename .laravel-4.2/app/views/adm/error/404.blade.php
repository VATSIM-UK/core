@extends('adm.error.default')

@section('error_code')
404
@stop

@section('error_title')
We lost the page...
@stop

@section('error_content')
<p>
    Our system seems to <strike>have lost</strike> be temporarily unsure of the location of the page you requested. We'll try and find it soon, honest.
</p>
@stop