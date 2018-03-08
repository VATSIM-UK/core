<ul class="timeline">

    <?php $date = ''; $colours = ['red', 'blue', 'green', 'orange', 'fuchsia', 'maroon']; ?>

    @foreach($activities as $activity)
        @if($date != \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $activity->created_at)->toFormattedDateString())
            <li class="time-label">
                <span class="bg-{{ $colours[array_rand($colours)] }}">
                    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $activity->created_at)->toFormattedDateString() }}
                </span>
            </li>
            <?php $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $activity->created_at)
                                        ->toFormattedDateString(); ?>
        @endif
        <li>
            @include('adm.sys.activity._event', ["activity" => $activity])
        </li>
    @endforeach

</ul>