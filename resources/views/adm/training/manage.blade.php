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
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">Students</div>
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
                                        <p>No Accounts are asssigned to this waiting list.</p>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop