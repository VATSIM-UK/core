<li>
    <!-- timeline icon -->
    <i class="fa fa-microphone bg-orange"></i>
    <div class="timeline-item">
        <span class="time">
            <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toTimeString() }}
            <span>&nbsp;&nbsp;</span>
            <i class="fa fa-desktop"></i> {{ $entry->ip }}
        </span>

        <h3 class="timeline-header">
            <a href="{{ URL::to('/adm/booking/atc/'.$entry->owner_id) }}">{{ $entry->owner_display }}</a>
            {{ str_replace("{extra}", "<a href='" . URL::to('/adm/user/'.$entry->extra_id)."'>".$entry->entry_replace."</a>", $entry->entry) }}
        </h3>

        <div class="timeline-body">
            @if(is_array($entry->extra_data) OR is_object($entry->extra_data))
                @foreach($entry->extra_data as $key => $value)
                    @if($key == "booked_by")
                        <strong>{{ $key }}</strong>: {{ HTML::link('/adm/user/'.$value, $value) }}<br />
                    @else
                        <strong>{{ $key }}</strong>: {{ $value }}<br />
                    @endif
                @endforeach
            @else
                No extra data available.
            @endif
        </div>
    </div>
</li>