@extends('layout')

@section('content')

    <p>
        All current notifications are listed below. We have separated them into "Unread" and "read".

        @if($allowedToLeave)
            {!! link_to_route("mship.manage.dashboard", "Once finished you can return to the dashboard.") !!}
        @endif
    </p>

    @if(Session::has("force_notification_read_return_url"))
        <div class="alert alert-danger" role="alert">
            There are important notifications that require your immediate attention. You will <strong>not</strong> be
            able to login to any services until these have been read and accepted.
        </div>
    @endif

    <div class="row">

        <div class="col-md-12">
            <div class="panel panel-ukblue">
                @if($unreadNotifications->count() > 0)
                    <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Unread
                        Notifications
                    </div>

                    <div class="panel-group" id="unreadNotifications" role="tablist" aria-multiselectable="true">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-8">
                              @foreach($unreadNotifications as $notice)

                                  <div class="panel panel-{{ $notice->status == \App\Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE ? "danger" : ($notice->status == \App\Models\Sys\Notification::STATUS_IMPORTANT ? "warning" : "default") }}">
                                      <div class="panel-heading" role="tab" id="heading<?=$notice->id?>">
                                          <h4 class="panel-title">
                                              <a data-toggle="collapse" data-parent="#unreadNotifications"
                                                 href="#collapse<?=$notice->id?>" aria-expanded="false"
                                                 aria-controls="collapse<?=$notice->id?>">
                                                  [Effective: {{ $notice->effective_at }}] {{ $notice->title }}

                                                  @if($notice->status == \App\Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE)
                                                      <span class="label label-danger">Must Acknowledge before continuing</span>
                                                  @elseif($notice->status == \App\Models\Sys\Notification::STATUS_IMPORTANT)
                                                      <span class="label label-warning">Highly important</span>
                                                  @endif
                                              </a>
                                          </h4>
                                      </div>
                                      <div id="collapse<?=$notice->id?>" class="panel-collapse collapse" role="tabpanel"
                                           aria-labelledby="heading<?=$notice->id?>">
                                          <div class="panel-body">
                                              {!! nl2br($notice->content) !!}

                                              <form action="{{ route('mship.notification.acknowledge', $notice->id) }}"
                                                    method="POST" class="form-horizontal">
                                                  @csrf
                                              @if($notice->status == \App\Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE)
                                                  <div class="form-group">
                                                      <div class="col-sm-offset-5 col-sm-7">
                                                          <button type="submit" class="btn btn-danger">Confirm Read</button>
                                                      </div>
                                                  </div>
                                              @elseif($notice->status == \App\Models\Sys\Notification::STATUS_IMPORTANT)
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
                                              </form>
                                          </div>
                                      </div>
                                  </div>

                              @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="panel panel-ukblue">
                @if($readNotifications->count() > 0)
                    <div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Read Notifications
                    </div>

                    <div class="panel-group" id="readNotifications" role="tablist" aria-multiselectable="true">
                      <div class="row">
                          <div class="col-md-offset-2 col-md-8">
                            @foreach($readNotifications as $notice)

                                <div class="panel panel-{{ $notice->status == \App\Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE ? "danger" : ($notice->status == \App\Models\Sys\Notification::STATUS_IMPORTANT ? "warning" : "default") }}">
                                    <div class="panel-heading" role="tab" id="heading<?=$notice->id?>">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#readNotifications"
                                               href="#collapse<?=$notice->id?>" aria-expanded="false"
                                               aria-controls="collapse<?=$notice->id?>">
                                                [Effective: {{ $notice->effective_at }}] {{ $notice->title }}
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapse<?=$notice->id?>" class="panel-collapse collapse" role="tabpanel"
                                         aria-labelledby="heading<?=$notice->id?>">
                                        <div class="panel-body">
                                            @if ($notice->pivot->created_at)
                                            <div class="text-right"><em>You first read this notification
                                                    on {{ $notice->pivot->created_at }}</em></div>
                                            @endif
                                            {!! nl2br($notice->content) !!}
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                          </div>
                      </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop
