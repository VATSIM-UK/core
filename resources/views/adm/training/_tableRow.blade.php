<tr>
    <td>{{ $list }}</td>
    <td>{{ $list->accounts->count() }}</td>
    <td>{!! link_to_route('training.waitingList.show','Manage Waiting List', [$list]) !!}</td>
</tr>