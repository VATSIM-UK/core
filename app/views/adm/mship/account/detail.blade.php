@extends('adm.layout')

@section('content')
<div class="row">

    @include('adm.layout.messages')

    <div class="col-md-12">
        <div class="box box-tools">
            <div class="box-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li {{ $selectedTab == "basic" ? "class='active'" : "" }}><a href="#basic" role="tab" data-toggle="tab">Basic Details</a></li>
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/role"))
                        <li {{ $selectedTab == "role" ? "class='active'" : "" }}><a href="#role" role="tab" data-toggle="tab">Roles</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security"))
                        <li {{ $selectedTab == "security" ? "class='active'" : "" }}><a href="#security" role="tab" data-toggle="tab">Security Details</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/receivedEmails"))
                        <li {{ $selectedTab == "receivedEmails" ? "class='active'" : "" }}><a href="#receivedEmails" role="tab" data-toggle="tab">Received Emails</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/sentEmails"))
                        <li {{ $selectedTab == "sentEmails" ? "class='active'" : "" }}><a href="#sentEmails" role="tab" data-toggle="tab">Sent Emails</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/notes"))
                        <li {{ $selectedTab == "notes" ? "class='active'" : "" }}><a href="#notes" role="tab" data-toggle="tab">Notes</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/flags"))
                        <li {{ $selectedTab == "flags" ? "class='active'" : "" }}><a href="#flags" role="tab" data-toggle="tab">Review Flags</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/datachanges"))
                        <li {{ $selectedTab == "datachanges" ? "class='active'" : "" }}><a href="#datachanges" role="tab" data-toggle="tab">Data Changes</a></li>
                    @endif
                </ul>
                <br />

                <div class="tab-content">
                    <div class="tab-pane fade {{ $selectedTab == "basic" ? "in active" : "" }}" id="basic">

                        <div class="col-md-12">
                            <div class="btn-toolbar">
                                <div class="btn-group pull-right">
                                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/impersonate"))
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalImpersonate">Impersonate</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="clearfix">&nbsp;</div>

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
                                    @if($_account->hasPermission("adm/mship/account/view/email"))
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
                                    @endif
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
                                        {{ $account->status_string }}
                                    </div>
                                    <div class="form-group">
                                        <label for="state">State:</label>
                                        {{ $account->current_state }}<br />
                                        @if($account->current_state)
                                            <em>({{ $account->current_state->created_at->diffForHumans() }}, {{ $account->current_state->created_at }})</em>
                                        @endif
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

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/impersonate"))
                        <div class="modal fade" id="modalImpersonate" tabindex="-1" role="dialog" aria-labelledby="Impersonate" aria-hidden="true">
                            {{ Form::open(array("url" => URL::route("adm.mship.account.impersonate", $account->account_id), "target" => "_blank")) }}
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">Impersonate User</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            Clicking confirm will log you in as this user <strong>on the user facing side</strong> and log you out (if you're logged in).
                                        </p>
                                        <p>
                                            This access is provided on the proviso that you do not misuse this, and that it is for a valid purpose.  To that end, we
                                            monitor these and request that you enter a reason in the box below.
                                        </p>
                                        <p>
                                        <div class="form-group">
                                            {{ Form::Label("reason", "Reason") }}
                                            {{ Form::textarea("reason", null, ["class" => "form-control"]) }}
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/role"))
                        <div class="tab-pane fade {{ $selectedTab == "role" ? "in active" : "" }}" id="role">
                            <!-- general form elements -->
                            <div class="box box-primary">

                                    <div class="box-header">
                                        <h3 class="box-title">Roles</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">

                                        <div class="btn-toolbar">
                                            <div class="btn-group pull-right">
                                                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/role/attach"))
                                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalRoleAttach">Add / Attach</button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="clearfix">&nbsp;</div>

                                        <table class="table table-striped table-bordered table-condensed">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th># Permissions</th>
                                                    <th>Added</th>
                                                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/role/".$account->account_id."/detach"))
                                                        <th>Delete</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($account->roles as $r)
                                                <tr>
                                                    <td>{{ $r->role_id }}</td>
                                                    <td>{{ $r->name }}</td>
                                                    <td>{{ count($r->permissions) }}</td>
                                                    <td>{{ $r->created_at->toDateTimeString() }}</td>
                                                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/role/".$r->role_id."/detach"))
                                                        <td>{{ Form::button("Delete", ["data-href" => URL::route("adm.mship.account.role.detach", [$account->account_id, $r->role_id]), "data-toggle" => "confirmation", "class" => "btn btn-xs btn-danger"]) }}</td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    @endif

                    <!-- Modals -->
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/role/attach"))
                        <div class="modal fade" id="modalRoleAttach" tabindex="-1" role="dialog" aria-labelledby="Role Attach" aria-hidden="true">
                            {{ Form::open(array("url" => URL::route("adm.mship.account.role.attach", $account->account_id))) }}
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">Add / Attach Role</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            To add a new role to this user's account, please choose from the dropdown below.  If the user already has this role
                                            it will *not* be added again.
                                        </p>
                                        <p>
                                        <div class="form-group">
                                            <label for="role">Role</label>
                                            <select name="role">
                                                @foreach($availableRoles as $ar)
                                                <option value="{{ $ar->role_id }}">{{ $ar->name }}</option>
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security"))
                        <div class="tab-pane fade {{ $selectedTab == "security" ? "in active" : "" }}" id="security">
                            <!-- general form elements -->
                            <div class="box box-primary">

                                <div class="box-header">
                                    <h3 class="box-title">Security</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">

                                    <div class="btn-toolbar">
                                        <div class="btn-group pull-right">
                                            @if($account->current_security)
                                                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/reset"))
                                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalSecurityReset">Reset Password</button>
                                                @endif

                                                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/change"))
                                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalSecurityChange">Change Level</button>
                                                @endif
                                            @else
                                                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/enable"))
                                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalSecurityEnable">Enable / Force</button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <div class="clearfix">&nbsp;</div>

                                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/view"))
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
                                    @endif
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    @endif

                    <!-- Modals -->
                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/enable"))
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/reset"))
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/security/change"))
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/receivedEmails"))
                        <div class="tab-pane fade {{ $selectedTab == "receivedEmails" ? "in active" : "" }}" id="receivedEmails">
                            <p>Only the last 25 emails this user has received have been displayed here.</p>
                            @include('adm.sys.postmaster.queue.widget', array('queue' => $account->messagesReceived()->limit(25)->get()))
                        </div>
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/sentEmails"))
                        <div class="tab-pane fade {{ $selectedTab == "sentEmails" ? "in active" : "" }}" id="sentEmails">
                            <p>Only the last 25 emails this user has sent have been displayed here.</p>
                            @include('adm.sys.postmaster.queue.widget', array('queue' => $account->messagesSent()->limit(25)->get()))
                        </div>
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/notes"))
                        <div class="tab-pane fade {{ $selectedTab == "notes" ? "in active" : "" }}" id="notes">
                            <div class="col-md-12">
                                <!-- general form elements -->
                                <div class="box box-primary">
                                    <div class="box-header">
                                        <h3 class="box-title">Notes</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">

                                        <div class="btn-toolbar">
                                            <div class="btn-group pull-right">
                                                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/note/filter"))
                                                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalNoteFilter">Change Filter</button>
                                                @endif

                                                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/note/create"))
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalNoteCreate">Add Note</button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="clearfix">&nbsp;</div>

                                        @if($_account->hasPermission("adm/mship/account/".$account->account_id."/note/view"))
                                            @foreach($account->notes as $note)
                                                @if((array_key_exists($note->note_type_id, Input::get("filter", [])) && count(Input::get("filter")) > 0) OR count(Input::get("filter")) < 1)
                                                    <div class="panel panel-{{ $note->type->colour_code }} note-{{ $note->type->is_system ? "system" : "" }} note-type-{{ $note->note_type_id }}" id='note-{{ $note->account_note_id }}'>
                                                        <div class="panel-heading">
                                                            <h3 class="panel-title">
                                                                {{ $note->type->name }}
                                                                <span class="time pull-right">
                                                                    <small>
                                                                        <i class="fa fa-user"></i>
                                                                        {{ $note->writer->name }} ({{ link_to_route("adm.mship.account.details", $note->writer_id, [$note->writer_id]) }})

                                                                        <i class="fa fa-clock-o"></i>
                                                                        {{ $note->created_at->diffForHumans() }}, {{ $note->created_at->toDateTimeString() }}
                                                                    </small>
                                                                </span>
                                                            </h3>
                                                        </div>
                                                        <div class="panel-body">
                                                            {{ $note->content }}
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal fade" id="modalNoteFilter" tabindex="-1" role="dialog" aria-labelledby="Filter Notes" aria-hidden="true">
                        {{ Form::open(array("url" => URL::route("adm.mship.account.note.filter", $account->account_id))) }}
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Note Filter</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        @foreach($noteTypesAll as $nt)
                                        <div class="col-sm-4">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="filter[]" value="{{ $nt->note_type_id }}" {{ Input::get("filter.".$nt->note_type_id) ? "checked='checked'" : "" }} />
                                                    {{ $nt->name }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Apply Filter</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/note/create"))
                        <div class="modal fade" id="modalNoteCreate" tabindex="-1" role="dialog" aria-labelledby="Create Note" aria-hidden="true">
                            {{ Form::open(array("url" => URL::route("adm.mship.account.note.create", $account->account_id))) }}
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">Add New Note</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            You may add a new note to a user's account by completing the form below.
                                        </p>
                                        <div class="form-group">
                                            <label for="note_type_id">Note Type</label>
                                            <select name="note_type_id" class="form-control selectpicker">
                                                @foreach($noteTypes as $nt)
                                                <option value="{{ $nt->note_type_id }}">
                                                    {{ $nt->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="content">Content</label>
                                            <textarea name="content" class="form-control" rows="5"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Add Note</button>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/flags"))
                        <div class="tab-pane fade {{ $selectedTab == "flags" ? "in active" : "" }}" id="flags">Review Flags</div>
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/datachanges"))
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
                                                <td>{{ $_account->hasPermission("adm/mship/account/".$account->account_id."/datachanges/view") ? $dc->data_old : "[No Permission]" }}</td>
                                                <td>{{ $_account->hasPermission("adm/mship/account/".$account->account_id."/datachanges/view") ? $dc->data_new : "[No Permission]" }}</td>
                                                <td>{{ $dc->created_at }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->

                        </div>
                    @endif

                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    @if($_account->hasPermission("adm/mship/account/".$account->account_id."/timeline"))
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
    @endif
</div>
@stop

@section('scripts')
@parent

<script language="javascript" type="text/javascript">
    function test(el) {
        console.log(el);
    }

    $(document).ready(function() {
        $('input[type="checkbox"]').click(function() {
            var item = $(this).attr('name');
            console.log(item);
        });
    });
</script>

@stop