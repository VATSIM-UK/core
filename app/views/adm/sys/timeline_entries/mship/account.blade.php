<li>
        <!-- timeline icon -->
        <i class="fa fa-user bg-aqua"></i>
        <div class="timeline-item">
            <span class="time">
                <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toTimeString() }}
                <span>&nbsp;&nbsp;</span>
                <i class="fa fa-desktop"></i> {{ $entry->ip }}
            </span>

            <h3 class="timeline-header">
                {{ str_replace("{owner}", "<a href=''>".$entry->owner_display."</a>", str_replace("{extra}", "<a href=''>".$entry->extra_display."</a>", $entry->entry)) }}
            </h3>

            <div class="timeline-body">
                @foreach($entry->extra_data as $k => $ed)
                    <strong>{{ ucwords($k) }}</strong>: <strong>{{ $ed  }}</strong>
                    @if(last($entry->extra_data) != $ed)
                        <br />
                    @endif
                @endforeach
            </div>
        </div>
    </li>