@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'References & Referees',
                'icon' => ['type' => 'fa', 'key' => 'comment-o'],
                'attr' => []
            ])
            <div class="row">

                <div class="col-md-6 col-md-offset-3">

                    <p>
                        <span id="minReferencesHelp">
                            Your application <strong>must be supported</strong> by a <strong>minimum</strong> of
                            {{ $application->references_required }} referee(s).
                        </span>

                        @if($application->number_references_required_relative)
                            You still need to add
                            <strong>{{ $application->number_references_required_relative }}</strong> more referee(s).
                        @else
                            You cannot add any more referees.
                        @endif
                    </p>
                    <p>
                        <span id="divisionStaffHelp">Your referees <strong>must</strong> be staff members in your home division</span>,
                        and <span id="trainingStaffHelp"><strong>one must</strong> be your Training Director</span>.
                    </p>
                    <p><br/></p>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        @if($application->number_references_required_relative > 0)
                            <form action="{{ route('visiting.application.referees.post', $application->public_id) }}"
                                  method="POST">
                                @csrf

                            <div class="col-md-6">
                                <div id="refereeCidHelp">
                                    <label for="referee_cid">Referee CID</label>
                                    <input type="text" name="referee_cid" class="form-control">
                                    <small class="form-text text-muted">Please ensure this is correct.</small>
                                </div>

                                <div id="refereePositionHelp">
                                    <label for="referee_relationship">Staff Position</label>
                                    <select name="referee_relationship" class="form-control">
                                        <option value="Region Director">Region Director</option>
                                        <option value="Region Staff">Region Staff</option>
                                        <option value="Division Director">Division Director</option>
                                        <option value="Division Training Director">Division Training Director</option>
                                        <option value="Division Staff">Division Staff</option>
                                        <option value="VACC/ARTCC Director">VACC/ARTCC Director</option>
                                        <option value="VACC/ARTCC Training Director">VACC/ARTCC Training Director</option>
                                        <option value="VACC/ARTCC Staff">VACC/ARTCC Staff</option>
                                    </select>
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div id="refereeEmail">
                                    <label for="referee_email">Email Address</label>
                                    <input type="text" name="referee_email" class="form-control">
                                    <small class="form-text text-muted">This should be the member's staff email address.</small>
                                </div>

                                <div class="text-center" style="padding-top: 27px;">
                                    <button type="submit" class="btn btn-primary">ADD REFEREE</button>
                                </div>
                            </div>

                            </form>
                        @else
                            <div class="col-md-12 text-center">
                                <p>You cannot add any additional referees at this time.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>CID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th class="col-md-1">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($application->referees as $referee)
                            <tr>
                                <td>{{ $referee->account_id }}</td>
                                <td>{{ $referee->account->name }}</td>
                                <td>{{ $referee->email }}</td>
                                <td>{{ $referee->relationship }}</td>
                                <td>
                                    <form
                                        action="{{ route('visiting.application.referees.delete.post', [$application->public_id, $referee->id]) }}"
                                        method="POST">
                                        @csrf
                                    <button type="submit" class="btn btn-danger btn-xs">DELETE</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            @include('components.html.panel_close')
        </div>
    </div>
@stop
