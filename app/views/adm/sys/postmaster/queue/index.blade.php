@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header">
                <div class="box-title">Search Criteria</div>
            </div>
            <div class="box-body">

            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Search Results
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div align="center">
                        {{ $queue->links() }}
                    </div>
                </div>
                <table id="mship-accounts" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Recipient</th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th style="text-align: center;">Priority</th>
                            <th style="text-align: center;">Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($queue as $q)
                        <tr>
                            <td>
                                @if($q->status != \Models\Sys\Postmaster\Queue::STATUS_PENDING)
                                    {{ link_to_route('adm.sys.postmaster.queue.view', $q->postmaster_queue_id, [$q->postmaster_queue_id]) }}
                                @else
                                    {{ $q->postmaster_queue_id }}
                                @endif
                            </td>
                            <td>{{ $q->recipient->name }} ({{ link_to_route("adm.mship.account.details", $q->recipient_id, [$q->recipient_id]) }})</td>
                            <td>{{ $q->sender->name }} ({{ link_to_route("adm.mship.account.details", $q->sender_id, [$q->sender_id]) }})</td>
                            <td>{{ Str::limit($q->subject, 25) }}</td>
                            <td align="center">
                                @if($q->priority == \Models\Sys\Postmaster\Template::PRIORITY_LOW)
                                <span class="label label-default">Low</span>
                                @elseif($q->priority == \Models\Sys\Postmaster\Template::PRIORITY_MED)
                                <span class="label label-primary">Normal</span>
                                @elseif($q->priority == \Models\Sys\Postmaster\Template::PRIORITY_HIGH)
                                <span class="label label-warning">High</span>
                                @elseif($q->priority == \Models\Sys\Postmaster\Template::PRIORITY_NOW)
                                <span class="label label-danger">Immediate</span>
                                @endif
                            </td>
                            <td align="center">
                                @if($q->status == \Models\Sys\Postmaster\Queue::STATUS_PENDING)
                                <span class="label label-default">Pending</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_PARSED)
                                <span class="label label-primary">Parsed</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_SENT)
                                <span class="label label-success">Sent</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_DELAYED)
                                <span class="label label-warning">Delayed</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_REJECTED)
                                <span class="label label-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $q->created_at }}</td>
                            <td>{{ $q->updated_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="row">
                    <div align="center">
                        {{ $queue->links() }}
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