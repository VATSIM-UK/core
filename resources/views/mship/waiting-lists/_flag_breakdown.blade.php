<span>Requirements:</span>
<ul>
    @foreach($flag->positionGroup->conditions as $condition)
        @php
            $progress = $condition->progressForUser($user);
            $overallProgress = $condition->overallProgressForUser($user);
        @endphp
        <li>{!! $condition->human_description !!}

            <ul>
                <li>Qualifying Positions: {{implode(", ",$condition->human_positions)}}</li>

                <li>Your progress ({{round($overallProgress, 1)}} of {{$condition->required_hours}} hours required)

                    @foreach($progress as $index => $hours)
                        <x-progress-indicator :value="$hours" :max="$condition->required_hours" :text="$index . ' - ' . round($hours,1) . ' hours'"/>
                    @endforeach
                </li>
            </ul>
        </li>
    @endforeach
</ul>
