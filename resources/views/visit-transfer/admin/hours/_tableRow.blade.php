<tr @if (( $account->networkDataAtc->sum('minutes_online') > 0 && $account->networkDataAtcUk->sum('minutes_online') / $account->networkDataAtc->sum('minutes_online') * 100) > 49)
        class="bg-danger"
    @endif>
    <td>{!! link_to_route('adm.mship.account.details', $account->id, $account->id) !!}</td>
    <td>{{ $account->name }}</td>
    <td>{{ $account->qualificationAtc }}</td>
    <td>{{ $account->primaryState->pivot->region }} / {{ $account->primaryState->pivot->division }} </td>
    <td>
        {{ minutesToHours($account->networkDataAtcUk->sum('minutes_online')) }} /
        {{ minutesToHours($account->networkDataAtc->sum('minutes_online')) }}
    </td>
</tr>