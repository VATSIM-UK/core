<div class="panel panel-ukblue">
    <div class="panel-heading">
        <i class="fa {{isset($icon) ? $icon : 'fa-list'}}"></i> {{isset($title) ? $title : 'Waiting Lists'}}
    </div>
    <div class="panel-body">
        <div class="row pl-4 pr-4">
            <table class="table text-center">
                <thead>
                <tr>
                    <th class="text-center">Waiting List</th>
                    <th class="text-center">Position</th>
                    <th class="text-center">Meeting Criteria</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Joined At</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($waitingLists as $waitingList)
                    <tr>
                        <td>{{$waitingList->name}}</td>
                        <td>{{$waitingList->pivot->position ?? '-'}}</td>
                        <td>
                            <x-boolean-indicator :value="$waitingList->pivot->eligibility"/>
                        </td>
                        <td>{{$waitingList->pivot->current_status}}</td>
                        <td>{{$waitingList->pivot->created_at}}</td>
                        <td><a href="{{route('mship.waiting-lists.view', ["waitingListId" => $waitingList->id])}}">View Details</a></td>
                    </tr>
                @endforeach
                @if(count($waitingLists) == 0)
                    <tr>
                        <td colspan="6">Your aren't in any waiting lists at the moment.</td>
                    </tr>
                @endempty
                </tbody>
            </table>
        </div>
    </div>
</div>
