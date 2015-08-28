@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Queue Entry # {{ $queue->postmaster_queue_id }}

                    @if($queue->priority == \App\Models\Sys\Postmaster\Template::PRIORITY_LOW)
                    <span class="label label-default">Priority: Low</span>
                    @elseif($queue->priority == \App\Models\Sys\Postmaster\Template::PRIORITY_MED)
                    <span class="label label-primary">Priority: Normal</span>
                    @elseif($queue->priority == \App\Models\Sys\Postmaster\Template::PRIORITY_HIGH)
                    <span class="label label-warning">Priority: High</span>
                    @elseif($queue->priority == \App\Models\Sys\Postmaster\Template::PRIORITY_NOW)
                    <span class="label label-danger">Priority: Immediate</span>
                    @endif

                    @if($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_PENDING)
                    <span class="label label-default">Pending</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_PARSED)
                    <span class="label label-primary">Parsed</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_DISPATCHED)
                    <span class="label label-success">Sent</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_DELIVERED)
                    <span class="label label-success">Delivered</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_CLICKED)
                    <span class="label label-success">Clicked</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_OPENED)
                    <span class="label label-success">Opened</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_DROPPED)
                    <span class="label label-warning">Dropped</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_SPAM)
                    <span class="label label-warning">Spam</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_UNSUBSCRIBED)
                    <span class="label label-warning">Unsubscribed</span>
                    @elseif($queue->status == \App\Models\Sys\Postmaster\Queue::STATUS_BOUNCED)
                    <span class="label label-danger">Bounced</span>
                    @endif
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{ $queue->subject }}</h3>
                    </div>
                    <div class="panel-body">
                        {{ nl2br($queue->body) }}
                    </div>
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>


    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Recent Timeline Events</h3>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                @include('adm.sys.timeline.widget', array('entries' => $queue->timeline_entries_recent))
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent

@stop