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

                        @if($department === \App\Models\Training\WaitingList::ATC_DEPARTMENT)
                            <th class="text-center">On Roster</th>
                            <th class="text-center">Theory Exam Passed</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waitingListAccounts as $waitingListAccount)
                    <tr>
                        <td>{{$waitingListAccount->waitingList->name}}</td>
                        <td>
                            @if($waitingListAccount->position)
                            {{$waitingListAccount->position}}
                            @else
                            -
                            @endif
                        </td>
                        <td>{{$waitingListAccount->created_at->format('d M Y')}}</td>

                        @if($department === \App\Models\Training\WaitingList::ATC_DEPARTMENT)
                            @if($waitingListAccount->waitingList->isAtcList())
                                <td>
                                    @if ($waitingListAccount->account->onRoster())
                                        {!! HTML::img("tick_mark_circle", "png", 20) !!}
                                    @else
                                        {!! HTML::img("cross_mark_circle", "png", 20) !!}
                                    @endif
                                </td>
                            @else
                                <td>
                                    N/A
                                </td>
                            @endif
                            <td>
                                @if ($waitingListAccount->waitingList->should_check_cts_theory_exam && $waitingListAccount->theory_exam_passed)
                                    {!! HTML::img("tick_mark_circle", "png", 20) !!}
                                @elseif($waitingListAccount->waitingList->should_check_cts_theory_exam)
                                    {!! HTML::img("cross_mark_circle", "png", 20) !!}
                                @else
                                    N/A
                                @endif
                            </td>
                        @endif
                    </tr>
                    @endforeach
                    @if(count($waitingListAccounts) == 0)
                    <tr>
                        <td colspan="6">You aren't in any waiting lists at the moment.</td>
                    </tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </div>
</div>
