@php

$percentage = 0;

if($account->networkDataAtc->sum('minutes_online') > 0) {
    $percentage = round($account->networkDataAtcUk->sum('minutes_online') / $account->networkDataAtc->sum('minutes_online') * 100, 2);
}

@endphp

<tr
        @if ($percentage > 49)
            class="bg-danger"
        @endif
>
    <td>{{ $account->id }}</td>
    <td>{{ $account->name }}</td>
    <td>{{ $account->qualificationAtc }}</td>
    <td>{{ $account->primaryState->pivot->region }} / {{ $account->primaryState->pivot->division }} </td>
    <td>
        {{ minutesToHours($account->networkDataAtcUk->sum('minutes_online')) }} /
        {{ minutesToHours($account->networkDataAtc->sum('minutes_online')) }}
    </td>
    <td>
        {{ $percentage }}%
    </td>
</tr>
