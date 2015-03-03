<ul class="timeline">
    <?php $date = ""; $colours = ['red', 'blue', 'green', 'orange', 'fuchsia', 'maroon']; ?>
    @foreach($entries as $entry)
        @if($date != \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toFormattedDateString())
            <li class="time-label">
                <span class="bg-{{ $colours[array_rand($colours)] }}">
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toFormattedDateString() }}
                </span>
            </li>
            <?php $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $entry->created_at)->toFormattedDateString(); ?>
        @endif
        <li>
            @if(View::exists('adm.sys.timeline_entries.'.$entry->action->type))
                @include('adm.sys.timeline_entries.'.$entry->action->type, array('entry' => $entry))
            @else
                No view defined for {{ $entry->action ? $entry->action->type : "UNKNOWN ACTION" }}. Probably should let web support know.
            @endif
        </li>
    @endforeach
</ul>