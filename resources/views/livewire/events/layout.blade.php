@extends('layout')

@section('styles')
	@vite('resources/assets/css/events.css')
@endsection

@section('content')
	{{ $slot }}
@endsection
