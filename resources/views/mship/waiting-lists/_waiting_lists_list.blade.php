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
                            @if($waitingListAccount->waitingList->isAtcList() && ($waitingListAccount->waitingList->feature_toggles["display_on_roster"] ?? true))
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

            @if ($department === \App\Models\Training\WaitingList::ATC_DEPARTMENT)
            <div>
                <h3>Self-enrolment</h3>
                <p>Some waiting lists have the ability to 'self-enrol' without having to contact the Training team.
                    If you are eligible to self-enrol, you will see a button below. Please note that this is only available for some waiting lists.</p>

                <table class="table text-center">
                    <thead>
                        <tr>
                            <th class="text-center">Waiting List</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selfEnrolWaitingLists as $waitingList)
                        <tr>
                            <td>{{$waitingList->name}}</td>
                            <td>
                                <form action="{{route('mship.waiting-lists.self-enrol', ['waitingList' => $waitingList])}}" method="POST">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="btn btn-primary" type="submit">Self Enrol</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @empty($waitingList)
                        <tr>
                            <td colspan="2">You are not eligible to self-enrol on any waiting lists.</td>
                        @endempty
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
