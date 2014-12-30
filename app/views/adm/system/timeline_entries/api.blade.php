<li>
        <!-- timeline icon -->
        <i class="fa fa-share-alt bg-green"></i>
        <div class="timeline-item">
            <span class="time">
                <i class="fa fa-clock-o"></i> {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toTimeString() }}
                <span>&nbsp;&nbsp;</span>
                <i class="fa fa-desktop"></i> {{ HTML::link('/adm/system/timeline?ip='.$entry->ip, $entry->ip) }}
            </span>

            <h3 class="timeline-header">
                <a href="{{ URL::to('/adm/api/account/'.$entry->owner_id) }}">{{ $entry->owner_display }}</a>
                @if($entry->extra instanceof \Models\Api\Account)
                    {{ str_replace("{extra}", "<a href='" . URL::to('/adm/api/account/'.$entry->extra_id)."'>".$entry->entry_replace."</a>", $entry->entry) }}
                @elseif($entry->extra instanceof \Models\Api\Permission)
                    {{ str_replace("{extra}", "<a href='" . URL::to('/adm/api/permission/'.$entry->extra_id)."'>".$entry->entry_replace."</a>", $entry->entry) }}
                @else
                    {{ str_replace("{extra}", $entry->entry_replace, $entry->entry) }}
                @endif
            </h3>

            <div class="timeline-body">
                @if(is_array($entry->extra_data) OR is_object($entry->extra_data))
                    @foreach($entry->extra_data as $key => $value)
                        @if(is_object($value))
                            @foreach($value as $key2 => $value2)
                                <strong>{{ $key."::".$key2 }}</strong>: {{ $value2 }}<br />
                            @endforeach
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