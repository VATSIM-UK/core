@php
    $flag = false;

    $percentage = $account->networkDataAtc->filter(function($value, $key) {
            return $value->uk_session;
    })->sum('minutes_online') / $account->networkDataAtc->sum('minutes_online') * 100;

    if ($percentage > 49) {
        $flag = true;
    }

@endphp

<tr @if ($flag === true) class="bg-danger" @endif>
    <td>{{ $account->id  }}</td>
    <td>{{ $account->name }}</td>
    <td>{{ $account->qualificationAtc }}</td>
    <td>{{ $account->primaryState->pivot->region }} / {{ $account->primaryState->pivot->division }} </td>
    <td>{{ date("H:i", mktime(0, $account->networkDataAtc->sum('minutes_online'))) }} /
        {{--{{ date("H:i", mktime(0, )) }}</td>--}}
        {{ date("H:i", mktime(0, $account->networkDataAtc->filter(function($value, $key) {
            return $value->uk_session;
        })->sum('minutes_online'))) }}</td>
</tr>