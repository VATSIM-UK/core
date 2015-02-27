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
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_DELIVERED)
                                <span class="label label-success">Delivered</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_CLICKED)
                                <span class="label label-success">Clicked</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_OPENED)
                                <span class="label label-success">Opened</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_DROPPED)
                                <span class="label label-warning">Dropped</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_SPAM)
                                <span class="label label-warning">Spam</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_UNSUBSCRIBED)
                                <span class="label label-warning">Unsubscribed</span>
                                @elseif($q->status == \Models\Sys\Postmaster\Queue::STATUS_BOUNCED)
                                <span class="label label-danger">Bounced</span>
                                @endif
                            </td>
                            <td>{{ $q->created_at }}</td>
                            <td>{{ $q->updated_at }}</td>
                        </tr>
                        @endforeach
                        @if(count($queue) < 1)
                        <tr>
                            <td colspan="8" align="center">Uh oh! There are no emails to display :(</td>
                        </tr>
                        @endif
                    </tbody>
                </table>