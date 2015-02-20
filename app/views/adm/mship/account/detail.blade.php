@extends('adm.layout')

@section('content')
<div class="row">

    @include('adm.layout.messages')

    <div class="col-md-12">
        <div class="box box-tools">
            <div class="box-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li {{ $selectedTab == "basic" ? "class='active'" : "" }}><a href="#basic" role="tab" data-toggle="tab">Basic Details</a></li>
                    <li {{ $selectedTab == "security" ? "class='active'" : "" }}><a href="#security" role="tab" data-toggle="tab">Security Details</a></li>
                    <li {{ $selectedTab == "notes" ? "class='active'" : "" }}><a href="#notes" role="tab" data-toggle="tab">Notes</a></li>
                    <li {{ $selectedTab == "flags" ? "class='active'" : "" }}><a href="#flags" role="tab" data-toggle="tab">Review Flags</a></li>
                    <li {{ $selectedTab == "datachanges" ? "class='active'" : "" }}><a href="#datachanges" role="tab" data-toggle="tab">Data Changes</a></li>
                </ul>
                <br />

                <div class="tab-content">
                    <div class="tab-pane fade {{ $selectedTab == "basic" ? "in active" : "" }}" id="basic">
                        <div class="col-md-4">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Basic Details</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="account_id">CID:</label>
                                        {{ $account->account_id }}
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Name:</label>
                                        {{ $account->name }}
                                    </div>
                                    <div class="form-group">
                                        <label for="primary_email">Primary Email:</label>
                                        {{ $account->primary_email }}
                                    </div>
                                    <div class="form-group">
                                        <label for="secondary_email">Secondary Email(s):</label>
                                        @if(count($account->secondary_email) < 1)
                                        <em>There are no secondary emails.</em>
                                        @else
                                        <br />
                                        @foreach($account->secondary_email as $se)
                                        <em>{{ $se->email }}</em><br />
                                        @endforeach
                                        @endif
                                    </div>
                                </div><!-- /.box-body -->
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Membership Status</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="status">Status:</label>
                                        {{ $account->status }}
                                    </div>
                                    <div class="form-group">
                                        <label for="state">State:</label>
                                        {{ $account->current_state }}<br />
                                        <em>({{ $account->current_state->created_at->diffForHumans() }}, {{ $account->current_state->created_at }})</em>
                                    </div>
                                </div><!-- /.box-body -->
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Qualifications</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <table class="table table-striped table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Code</th>
                                                <th>Achieved</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($account->qualifications as $q)
                                            <tr>
                                                <td>{{ $q->qualification->type }}</td>
                                                <td>{{ $q->qualification->code }}</td>
                                                <td>{{ $q->created_at->diffForHumans() }}, {{ $q->created_at }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>
                    <div class="tab-pane fade {{ $selectedTab == "security" ? "in active" : "" }}" id="security">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Security Setting History</h3>
                            </div><!-- /.box-header -->
                            <div class="box-body">

                                <div class="btn-toolbar">
                                    <div class="btn-group pull-right">
                                        @if($account->current_security)
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalSecurityReset">Reset Password</button>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalSecurityChange">Change Level</button>
                                        @else
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalSecurityEnable">Enforce</button>
                                        @endif
                                    </div>
                                </div>

                                <div class="clearfix">&nbsp;</div>

                                <table class="table table-striped table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>Security Level</th>
                                            <th>Created</th>
                                            <th>Expires</th>
                                            <th>Deleted?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($account->security()->withTrashed()->get() as $s)
                                        <tr>
                                            <td>{{ $s->security->name }}</td>
                                            <td>{{ $s->created_at->diffForHumans() }}, {{ $s->created_at }}</td>
                                            <td>{{ $s->expires_at == "0000-00-00 00:00:00" ? $s->expires_at->diffForHumans().", ".$s->expires_at : "Never"  }}</td>
                                            <td>{{ $s->deleted_at ? $s->deleted_at->diffForHumans().", ".$s->deleted_at : "Not Deleted" }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><!-- /.box-body -->
                        </div><!-- /.box -->

                    </div>

                    <!-- Modals -->
                    <div class="modal fade" id="modalSecurityEnable" tabindex="-1" role="dialog" aria-labelledby="Security Enable" aria-hidden="true">
                        {{ Form::open(array("url" => URL::route("adm.mship.account.security.enable", $account->account_id))) }}
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Enable Security Security</h4>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        You can enable the secondary security for a user by choosing the security level and clicking "Confirm".  Once you do this,
                                        the user will be requested to choose a secondary password on their next login to any of our Web Services.
                                    </p>
                                    <p>
                                    <div class="form-group">
                                        <label for="securityLevel">Security Level</label>
                                        <select name="securityLevel">
                                            @foreach($securityLevels as $sl)
                                                <option value="{{ $sl->security_id }}">{{ $sl->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Confirm</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <div class="modal fade" id="modalSecurityReset" tabindex="-1" role="dialog" aria-labelledby="Security Reset" aria-hidden="true">
                        {{ Form::open(array("url" => URL::route("adm.mship.account.security.reset", $account->account_id))) }}
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Reset Security Password</h4>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        You can reset {{ $account->name }}'s security password by clicking confirm. This will despatch an email to the user
                                        detailing how they can continue with the reset process.
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Confirm Reset</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <div class="modal fade" id="modalSecurityChange" tabindex="-1" role="dialog" aria-labelledby="Security Level Change" aria-hidden="true">
                        {{ Form::open(array("url" => URL::route("adm.mship.account.security.change", $account->account_id))) }}
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Change Security Level</h4>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        You can change a user's current security level by selecting the new level below and confirming this action.
                                        It's important to note that when you do this, it will force their current security to expire and a new one to be
                                        created for them.
                                    </p>
                                    <p>
                                    <div class="form-group">
                                        <label for="securityLevel">Security Level</label>
                                        <select name="securityLevel">
                                            @foreach($securityLevels as $sl)
                                                <option value="{{ $sl->security_id }}" {{ $account->current_security && $sl->security_id == $account->current_security->security_id ? "selected='selected'" : "" }}>
                                                    {{ $sl->name }}  {{ $account->current_security && $sl->security_id == $account->current_security->security_id ? "(current)" : "" }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Confirm Reset</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <div class="tab-pane fade {{ $selectedTab == "notes" ? "in active" : "" }}" id="notes_info">Notes</div>
                    <div class="tab-pane fade {{ $selectedTab == "flags" ? "in active" : "" }}" id="flags">Review Flags</div>
                    <div class="tab-pane fade {{ $selectedTab == "datachanges" ? "in active" : "" }}" id="datachanges">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Data Changes</h3>
                            </div><!-- /.box-header -->
                            <div class="box-body">
                                <table class="table table-striped table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>Data Key</th>
                                            <th>Old Value</th>
                                            <th>New Value</th>
                                            <th>Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($account->data_changes as $dc)
                                        <tr>
                                            <td>{{ $dc->data_key }}</td>
                                            <td>{{ $dc->data_old }}</td>
                                            <td>{{ $dc->data_new }}</td>
                                            <td>{{ $dc->created_at }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div><!-- /.box-body -->
                        </div><!-- /.box -->

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Qualifications</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Code</th>
                            <th>Name / GRP Name</th>
                            <th>Achieved</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($account->qualifications as $q)
                        <tr>
                            <td>{{ strtoupper($q->qualification->type) }}</td>
                            <td>{{ $q->qualification->code }}</td>
                            <td>{{ $q->qualification->name_long }} / {{ $q->qualification->name_grp }}</td>
                            <td>{{ $q->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>

    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Recent Timeline Events</h3>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                @include('adm.sys.timeline.widget', array('entries' => $account->timeline_entries_recent))
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
@stop