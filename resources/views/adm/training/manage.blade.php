@extends('adm.layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <div class="box-title">Managing Waiting List - {{ $waitingList }}</div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    Below are all of the students assigned to the waiting list.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="d-flex">
                                    @include('adm.layout.messages')
                                    <div class="h4 p-2">Students</div>
                                        @can('addAccount', $waitingList)
                                            <button type="button" class="btn btn-primary"
                                                    data-toggle="modal" data-target="#modalWaitingListAdd">Add Account</button>
                                        @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="accounts-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Account CID</th>
                                    <th>Name</th>
                                    <th>ATC Rating</th>
                                    <th>Added On</th>
                                    <th>Status</th>
                                    @if ($waitingList->isAtcList())<th>12 Hour Check</th> @endif
                                    <td>Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($waitingList->accounts as $account)
                                        <tr>
                                            <td><strong>{{ $account->pivot->position }}</strong></td>
                                            <td>{!! link_to_route('adm.mship.account.details', $account->id, $account->id) !!}</td>
                                            <td>{{ $account }}</td>
                                            <td>{{ $account->qualificationAtc }}</td>
                                            <td>{{ $account->pivot->created_at }}</td>
                                            <td>{{ $account->pivot->status->where('end_at', '==', null)->first() }}</td>
                                            @if ($waitingList->isAtcList())
                                                <td style="font-weight: 500; @if ($account->pivot->atcHourCheck()) background-color: green; @else background-color: indianred; @endif"> {{ $account->pivot->atcHourCheck() ? "Y": "N" }} </td>
                                            @endif
                                            <td>
                                                <div style="display: flex; justify-content: center">
                                                    @can('promoteAccount', $waitingList)
                                                        <div class="p-2 flex-fill" id="promote-button">
                                                            @if($account->id != $waitingList->accounts->first()->id)
                                                                {!! Form::open(['route' => ['training.waitingList.manage.promote', $waitingList], 'method' => 'POST']) !!}
                                                                    {!! Form::hidden('account_id', $account->id) !!}
                                                                    {!! Form::hidden('position', 1) !!}
                                                                    <button class="blank-button" type="submit">
                                                                        <i class="fa fa-arrow-up text-success promote" data-toggle="tooltip" data-placement="top" title="Promote Student"></i>
                                                                    </button>
                                                                {!! Form::close() !!}
                                                            @endif
                                                        </div>
                                                    @endcan
                                                    @can('demoteAccount', $waitingList)
                                                        <div class="flex-fill" id="demote-button">
                                                            @if($account->id != $waitingList->accounts->last()->id)
                                                                {!! Form::open(['route' => ['training.waitingList.manage.demote', $waitingList], 'method' => 'POST']) !!}
                                                                {!! Form::hidden('account_id', $account->id) !!}
                                                                {!! Form::hidden('position', 1) !!}
                                                                <button class="blank-button" type="submit">
                                                                    <i class="fa fa-arrow-down text-primary promote" data-toggle="tooltip" data-placement="top" title="Demote Student"></i>
                                                                </button>
                                                                {!! Form::close() !!}
                                                            @endif
                                                        </div>
                                                    @endcan
                                                    @can('removeAccount', $waitingList)
                                                        <div class="flex-fill" id="remove-button">
                                                            {!! Form::open(['route' => ['training.waitingList.remove', $waitingList], 'method' => 'POST']) !!}
                                                                {!! Form::hidden('account_id', $account->id) !!}
                                                                <button class="blank-button" type="submit">
                                                                    <i class="fa fa-times text-danger" data-toggle="tooltip" data-placement="top" title="Remove Student"></i>
                                                                </button>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                No accounts are assigned to this waiting list...
                                            </td>
                                        </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalWaitingListAdd" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    {!! Form::open(['route' => ['training.waitingList.store', $waitingList], 'method' => 'post']) !!}
                    <div class="modal-content">
                        <div class="modal-header">Add Account to Waiting List</div>
                        <div class="modal-body">
                            <div class="form-group">
                                {!! Form::label('account_id', 'Account CID') !!}
                                {!! Form::text('account_id','',['class' => 'form-control']); !!}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
@stop

@section('scripts')
    @parent
    <script type="text/javascript">

    </script>
@stop