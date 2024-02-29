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
                        <th class="text-center">Joined List</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waitingLists as $waitingList)
                    <tr>
                        <td>{{$waitingList->name}}</td>
                        <td>
                            We cannot currently show you your position in the waiting list due to GCAP implementation.
                            We expect this to very completed soon and the underlying data is still present i.e. your original position.
                        </td>
                        {{-- <td>
                            @if($waitingList->pivot->position)
                            {{$waitingList->pivot->position}}
                            @else
                            - <span class="fa fa-question-circle ml-2 text-info text-sm tooltip_displays" data-toggle="tooltip" data-placement="top" title="You might not have a position because you aren't meeting eligibility criteria. Once you are meeting the criteria, your position will be shown."></span>
                            @endif
                        </td> --}}
                        <td>{{$waitingList->pivot->created_at->format('d M Y')}}</td>
                        <td><a href="{{route('mship.waiting-lists.view', ["waitingListId" => $waitingList->id])}}">View Details</a></td>
                    </tr>
                    @endforeach
                    @if(count($waitingLists) == 0)
                    <tr>
                        <td colspan="6">You aren't in any waiting lists at the moment.</td>
                    </tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </div>
</div>
