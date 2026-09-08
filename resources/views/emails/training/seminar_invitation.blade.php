@extends('emails.messages.post')

@section('body')

    TEST TEXT REPLACE BEFORE PROD PLS DONT FORGET COBY

    <p>You are invited to an OBS &gt; S1 seminar.</p>

    <p>
        <strong>{{ $seminar->name }}</strong><br>
        Date: {{ $seminar->date->format('d/m/Y') }}<br>
        Time: {{ \Carbon\Carbon::parse($seminar->from)->format('H:i') }}Z - {{ \Carbon\Carbon::parse($seminar->to)->format('H:i') }}Z
    </p>

    <p>Please respond before <strong>{{ $invitation->expires_at->format('H:i \o\n d/m/Y') }}Z</strong>.</p>

    <p style="margin-top: 24px;">
        <a href="{{ $acceptUrl }}" class="btn btn-primary" style="margin-right: 12px;">Yes I'm coming</a>
        <a href="{{ $notInterestedUrl }}" class="btn"
            style="color: #fff; background-color: #d9534f; border-color: #d43f3a; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #d43f3a; margin-right: 12px;">
            I'm no longer interested
        </a>
        <a href="{{ $cannotAttendUrl }}" class="btn"
            style="color: #fff; background-color: #f0ad4e; border-color: #eea236; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #eea236;">
            I cannot attend
        </a>
    </p>
@stop
