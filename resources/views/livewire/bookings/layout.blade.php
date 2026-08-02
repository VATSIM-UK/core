@php
	$_bannerUrl ??= asset('images/banner.jpg');
@endphp

@extends('layout')

@section('styles')
	@vite('resources/assets/css/bookings-calendar.css')
@endsection

@section('scripts')
	@vite('resources/assets/js/bookings-calendar.js')
@endsection

@section('content')
	{{ $slot }}
@endsection
