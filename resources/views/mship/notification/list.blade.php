@extends('layout')

@section('content')

<p>
    All current notifications are listed below.  We have separated them into "Unread" and "read".

    @if($allowedToLeave)
        {{ link_to_route("mship.manage.dashboard", "Once finished you can return to the dashboard.") }}
    @endif
</p>

@if(Session::has("force_notification_read_return_url"))
    <div class="alert alert-danger" role="alert">
        There are important notifications that require your immediate attention.  You will <strong>not</strong> be able to login to any services until these have been read and accepted.
    </div>
@endif

<div class="row">

    <div class="col-md-12">

        @if($unreadNotifications->count() > 0)
            <h2>Unread Notifications</h2>

            <div class="panel-group" id="unreadNotifications" role="tablist" aria-multiselectable="true">

                @foreach($unreadNotifications as $notice)

                    <div class="panel panel-{{ $notice->status == \Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE ? "danger" : ($notice->status == \Models\Sys\Notification::STATUS_IMPORTANT ? "warning" : "default") }}">
                        <div class="panel-heading" role="tab" id="heading<?=$notice->notification_id?>">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#unreadNotifications" href="#collapse<?=$notice->notification_id?>" aria-expanded="false" aria-controls="collapse<?=$notice->notification_id?>">
                                    [Effective: {{ $notice->effective_at }}] {{ $notice->title }}

                                    @if($notice->status == \Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE)
                                        <span class="label label-danger">Must Acknowledge before continuing</span>
                                    @elseif($notice->status == \Models\Sys\Notification::STATUS_IMPORTANT)
                                        <span class="label label-warning">Highly important</span>
                                    @endif
                                </a>
                            </h4>
                        </div>
                        <div id="collapse<?=$notice->notification_id?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?=$notice->notification_id?>">
                            <div class="panel-body">
                                {{ nl2br($notice->content) }}

                                {!! Form::open(["route" => ["mship.notification.acknowledge", $notice->notification_id], "class" => "form-horizontal"]) !!}
                                    @if($notice->status == \Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE)
                                        <div class="form-group">
                                            <div class="col-sm-offset-5 col-sm-7">
                                                <button type="submit" class="btn btn-danger">Confirm Read</button>
                                            </div>
                                        </div>
                                    @elseif($notice->status == \Models\Sys\Notification::STATUS_IMPORTANT)
                                        <div class="form-group">
                                            <div class="col-sm-offset-5 col-sm-7">
                                                <button type="submit" class="btn btn-warning">Mark Read</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <div class="col-sm-offset-5 col-sm-7">
                                                <button type="submit" class="btn btn-default">Mark Read</button>
                                            </div>
                                        </div>
                                    @endif
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>

                @endforeach

            </div>
        @endif

        @if($readNotifications->count() > 0)
            <h2>Read Notifications</h2>

            <div class="panel-group" id="readNotifications" role="tablist" aria-multiselectable="true">

                @foreach($readNotifications as $notice)

                    <div class="panel panel-{{ $notice->status == \Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE ? "danger" : ($notice->status == \Models\Sys\Notification::STATUS_IMPORTANT ? "warning" : "default") }}">
                        <div class="panel-heading" role="tab" id="heading<?=$notice->notification_id?>">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#readNotifications" href="#collapse<?=$notice->notification_id?>" aria-expanded="false" aria-controls="collapse<?=$notice->notification_id?>">
                                    [Effective: {{ $notice->effective_at }}] {{ $notice->title }}
                                </a>
                            </h4>
                        </div>
                        <div id="collapse<?=$notice->notification_id?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?=$notice->notification_id?>">
                            <div class="panel-body">
                                <div class="text-right"><em>You first read this notification on {{ $notice->pivot->created_at }}</em></div>
                                {{ nl2br($notice->content) }}
                            </div>
                        </div>
                    </div>

                @endforeach
            </div>
        @endif

    </div>
</div>
@stop