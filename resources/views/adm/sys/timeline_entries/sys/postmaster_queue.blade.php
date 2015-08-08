<li>
    <!-- timeline icon -->
    <i class="fa fa-envelope-o"></i>
    <div class="timeline-item">
        <span class="time">
            <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toTimeString() }}
            <span>&nbsp;&nbsp;</span>
            <i class="fa fa-desktop"></i> {{ $entry->ip }}
        </span>

        <h3 class="timeline-header">
            {{ str_replace("{owner}", "<a href='".URL::route("adm.mship.account.details", [$entry->owner_id])."'>".$entry->owner_display."</a>", str_replace("{extra}", "<a href='".URL::route("adm.sys.postmaster.queue.view", [$entry->extra_id])."'>".$entry->extra_display."</a>", $entry->entry)) }}
        </h3>

        <div class="timeline-body">
            {{ json_decode($entry->action->entry) }}
        </div>
    </div>
</li>