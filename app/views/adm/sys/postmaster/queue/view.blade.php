@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Queue Entry # {{ $queue->postmaster_queue_id }}

                    @if($queue->priority == \Models\Sys\Postmaster\Template::PRIORITY_LOW)
                        <span class="label label-default">Priority: Low</span>
                    @elseif($queue->priority == \Models\Sys\Postmaster\Template::PRIORITY_MED)
                        <span class="label label-primary">Priority: Normal</span>
                    @elseif($queue->priority == \Models\Sys\Postmaster\Template::PRIORITY_HIGH)
                        <span class="label label-warning">Priority: High</span>
                    @elseif($queue->priority == \Models\Sys\Postmaster\Template::PRIORITY_NOW)
                        <span class="label label-danger">Priority: Immediate</span>
                    @endif

                    @if($queue->status == \Models\Sys\Postmaster\Queue::STATUS_PENDING)
                        <span class="label label-default">Status: Pending</span>
                    @elseif($queue->status == \Models\Sys\Postmaster\Queue::STATUS_PARSED)
                        <span class="label label-primary">Status: Parsed</span>
                    @elseif($queue->status == \Models\Sys\Postmaster\Queue::STATUS_SENT)
                        <span class="label label-success">Status: Sent</span>
                    @elseif($queue->status == \Models\Sys\Postmaster\Queue::STATUS_DELAYED)
                        <span class="label label-warning">Status: Delayed</span>
                    @elseif($queue->status == \Models\Sys\Postmaster\Queue::STATUS_REJECTED)
                        <span class="label label-danger">Status: Rejected</span>
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
</div>
@stop

@section('scripts')
@parent

@stop