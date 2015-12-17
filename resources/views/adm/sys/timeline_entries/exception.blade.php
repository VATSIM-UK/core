<li>
        <!-- timeline icon -->
        <i class="fa fa-warning bg-red"></i>
        <div class="timeline-item">
            <span class="time">
                <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toTimeString() }}
                <span>&nbsp;&nbsp;</span>
                <i class="fa fa-desktop"></i> {{ $entry->ip }}
            </span>

            <h3 class="timeline-header">
                {{ $entry->action->entry }}
            </h3>

            <div class="timeline-body">
                {{ json_decode($entry->action->entry) }}
            </div>
        </div>
    </li>