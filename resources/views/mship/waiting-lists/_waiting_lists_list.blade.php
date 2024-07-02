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
                        <th class="text-center">Joined List</th>
                        <th class="text-center">On Roster</th>
                        <th class="text-center">Theory Exam Passed</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waitingLists as $waitingList)
                    <tr>
                        <td>{{$waitingList->name}}</td>
                        <td>
                            @if($waitingList->pivot->position)
                            {{$waitingList->pivot->position}}
                            @else
                            -
                            @endif
                        </td>
                        <td>{{$waitingList->pivot->created_at->format('d M Y')}}</td>
                        <td>
                            @if ($waitingList->isAtcList() && $waitingList->pivot->account->onRoster())
                                {!! HTML::img("tick_mark_circle", "png", 20) !!}
                            @elseif($waitingList->isAtcList())
                                {!! HTML::img("cross_mark_circle", "png", 20) !!}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if (($record->waitingList->feature_toggles['check_cts_theory_exam'] ?? false) && $waitingList->pivot->theory_exam_passed)
                                {!! HTML::img("tick_mark_circle", "png", 20) !!}
                            @elseif($record->waitingList->feature_toggles['check_cts_theory_exam'] ?? false)
                                {!! HTML::img("cross_mark_circle", "png", 20) !!}
                            @else
                                N/A
                            @endif
                        </td>
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
