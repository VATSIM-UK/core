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
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalWaitingListAdd">Add Account</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="accounts-table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Account CID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Added On</th>
                                <td>Actions</td>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($waitingList->accounts as $account)
                                        <tr>
                                            <td>{!! link_to_route('adm.mship.account.details', $account->id, $account->id) !!}</td>
                                            <td>{{ $account }}</td>
                                            <td>{{ $account->pivot->position }}</td>
                                            <td>{{ $account->created_at }}</td>
                                            <td></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
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