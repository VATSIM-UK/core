<li>
    <!-- timeline icon -->
    <i class="fa {{ isset($entry_class) ? $entry_class : "fa-question" }}"></i>
    <div class="timeline-item">
            <span class="time">
                <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $activity->created_at)->toTimeString() }}
                <span>&nbsp;&nbsp;</span>
                <i class="fa fa-desktop"></i> {{ $activity->ip }}
            </span>

        <h3 class="timeline-header">
            @include("adm.partials._account_link", ["account" => $activity->actor]) {{ $activity->action }} an entry in {{ $activity->type }} with ID {{ $activity->subject_id }}.
        </h3>

        <div class="timeline-body">
            {{ json_encode($activity->extra_data) }}
        </div>
    </div>
</li>