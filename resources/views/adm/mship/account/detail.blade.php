@extends('adm.layout')

@section('content')
<div class="row">

    @include('adm.layout.messages')

    <div class="col-md-12">
        <div class="box box-tools">
            <div class="box-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li {{ $selectedTab == "basic" ? "class='active'" : "" }}><a href="#basic" role="tab" data-toggle="tab">Basic Details</a></li>
                    @can('use-permission', "adm/mship/account/*/roles")
                        <li {!! $selectedTab == "roles" ? "class='active'" : "" !!}><a href="#role" role="tab" data-toggle="tab">Roles</a></li>
                    @endcan
                    @can('use-permission', "adm/visit-transfer/application/*")
                        <li {!! $selectedTab == "vtapps" ? "class='active'" : "" !!}><a href="#vtapps" role="tab" data-toggle="tab">V/T Applications</a></li>
                    @endcan
                    @can('use-permission', "adm/mship/account/*/bans")
                        <li {!! $selectedTab == "bans" ? "class='active'" : "" !!}><a href="#bans" role="tab" data-toggle="tab">Bans</a></li>
                    @endcan
                    @can('use-permission', "adm/mship/account/*/notes")
                        <li {!! $selectedTab == "notes" ? "class='active'" : "" !!}><a href="#notes" role="tab" data-toggle="tab">Notes</a></li>
                    @endcan
                </ul>
                <br />

                <div class="tab-content">
                    <div class="tab-pane fade {{ $selectedTab == "basic" ? "in active" : "" }}" id="basic">

                        <div class="col-md-12">
                            <div class="btn-toolbar">
                                    <div class="btn-group pull-right" role="group">
                                    @can('use-permission', "adm/mship/account/*/impersonate")
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalImpersonate">Impersonate</button>
                                    @endcan
                                        <a href="{{route('adm.mship.account.sync', $account->id)}}" class="btn btn-warning">Request Central Update</a>
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
                                        <label for="id">CID:</label>
                                        {{ $account->id }}
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Name:</label>
                                        {{ $account->name }}
                                    </div>
                                    @can('use-permission', "adm/mship/account/email/view")
                                        <div class="form-group">
                                            <label for="primary_email">Primary Email:</label>
                                            {{ $account->email }}
                                        </div>
                                        <div class="form-group">
                                            <label for="secondary_email">Secondary Email(s):</label>
                                            @if(count($account->secondaryEmails) < 1)
                                            <em>There are no secondary emails.</em>
                                            @else
                                            <br />
                                            @foreach($account->secondaryEmails as $se)
                                            <em> - {{ $se->email }}</em><br />
                                            @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div><!-- /.box-body -->
                            </div>
                        </div>

                        <div class="col-md-4 danger">
                            <!-- general form elements -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h3 class="box-title">Membership Status - {{ $account->status_string }}</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <table class="table table-striped table-bordered table-condensed">
                                        <thead>
                                            <tr>
                                                <th>State</th>
                                                <th>Region</th>
                                                <th>Division</th>
                                                <th>Start</th>
                                                <th>End</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($account->statesHistory as $state)
                                                <tr>
                                                    <td>{{ $state->name }}</td>
                                                    <td>{{ $state->pivot->region }}</td>
                                                    <td>{{ $state->pivot->division }}</td>
                                                    <td>{{ $state->pivot->start_at }}</td>
                                                    <td>{{ $state->pivot->end_at }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                                                <td>{{ $q->type }}</td>
                                                <td>{{ $q->code }}</td>
                                                <td>{{ $q->pivot->created_at->diffForHumans() }}, {{ $q->pivot->created_at }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>

                    @can('use-permission', "adm/mship/account/*/impersonate")
                        <div class="modal fade" id="modalImpersonate" tabindex="-1" role="dialog" aria-labelledby="Impersonate" aria-hidden="true">
                            {!! Form::open(["url" => route("adm.mship.account.impersonate", $account->id), "target" => "_blank"]) !!}
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">Impersonate User</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            Clicking confirm will log you in as this user <strong>on the user facing side</strong> and log you out of your own account.
                                        </p>
                                        <p>
                                            This feature should only be used in rare and extreme circumstances. All impersonations are monitored,
                                            and may be followed up. Use of this feature must be authorized by the Web Systems Director or Web Support
                                            Director every time it is used.
                                        </p>
                                        <p>
                                            <strong>You MUST include the Helpdesk ticket reference in your reason.</strong>
                                        </p>
                                        <div class="form-group">
                                            {!! Form::Label("reason", "Reason") !!}
                                            {!! Form::textarea("reason", null, ["class" => "form-control"]) !!}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Confirm</button>
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endcan

                    @can('use-permission', "adm/mship/account/*/roles")
                        <div class="tab-pane fade {{ $selectedTab == "roles" ? "in active" : "" }}" id="role">
                            <!-- general form elements -->
                            <div class="box box-primary">

                                    <div class="box-header">
                                        <h3 class="box-title">Roles</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">

                                        <div class="btn-toolbar">
                                            <div class="btn-group pull-right">
                                                @can('use-permission', "adm/mship/account/*/roles/attach")
                                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalRoleAttach">Add / Attach</button>
                                                @endcan
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
                                                    @can('use-permission', "adm/mship/account/*/roles/*/detach")
                                                        <th>Delete</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($account->roles as $r)
                                                <tr>
                                                    <td>{{ $r->id }}</td>
                                                    <td>{{ $r->name }}</td>
                                                    <td>{{ count($r->permissions) }}</td>
                                                    <td>{{ $r->created_at->toDateTimeString() }}</td>
                                                    @can('use-permission', "adm/mship/account/*/roles/*/detach")
                                                        <td>{!! Form::button("Delete", ["data-href" => URL::route("adm.mship.account.role.detach", [$account->id, $r->id]), "data-toggle" => "confirmation", "class" => "btn btn-xs btn-danger"]) !!}</td>
                                                    @endcan
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    @endcan

                    @can('use-permission', "adm/visit-transfer/application/*")
                        <div class="tab-pane fade {{ $selectedTab == "vtapps" ? "in active" : "" }}" id="vtapps">
                            <!-- general form elements -->
                            <div class="box box-primary">

                                    <div class="box-header">
                                        <h3 class="box-title">Visit / Transfer Application History</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">
                                        <table class="table table-striped table-bordered table-condensed">
                                          <thead>
                                              <tr>
                                                  <th>ID</th>
                                                  <th>Type</th>
                                                  <th>Facility</th>
                                                  <th>Created</th>
                                                  <th>Updated</th>
                                                  <th>Status</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              @foreach($vtapplications as $a)
                                              <tr>
                                                <td>{!! link_to_route('adm.visiting.application.view', $a->public_id, [$a->id]) !!}</td>
                                                <td>{{ $a->type_string }}</td>
                                                <td>{{ $a->facility_name }}</td>
                                                <td>
                                                    {!! HTML::fuzzyDate($a->created_at) !!}
                                                </td>
                                                <td>
                                                    {!! HTML::fuzzyDate($a->updated_at) !!}
                                                </td>
                                                <td>
                                                    @include("visit-transfer.partials.application_status", ["application" => $a])
                                                </td>
                                              </tr>
                                              @endforeach
                                          </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    @endcan

                    <!-- Modals -->
                    @can('use-permission', "adm/mship/account/*/roles/attach")
                        <div class="modal fade" id="modalRoleAttach" tabindex="-1" role="dialog" aria-labelledby="Role Attach" aria-hidden="true">
                            {!! Form::open(["url" => URL::route("adm.mship.account.role.attach", $account->id)]) !!}
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
                                                <option value="{{ $ar->id }}">{{ $ar->name }}</option>
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
                            {!! Form::close() !!}
                        </div>
                    @endcan

                    @can('use-permission', "adm/mship/account/*/bans")
                        <div class="tab-pane fade {{ $selectedTab == "bans" ? "in active" : "" }}" id="bans">
                            <div class="col-md-12">
                                <!-- general form elements -->
                                <div class="box box-primary">
                                    <div class="box-header">
                                        <h3 class="box-title">Bans</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">

                                        <div class="btn-toolbar">
                                            <div class="btn-group pull-right">
                                                @if(!$account->is_banned)
                                                    @can('use-permission', "adm/mship/account/*/ban/add")
                                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalBanAdd">Add Ban</button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>

                                        <div class="clearfix">&nbsp;</div>

                                        @can('use-permission', "adm/mship/account/*/ban/view")
                                            @foreach($account->bans as $ban)
                                                @include("adm.mship.account._ban", ["ban" => $ban, "selectedTab" => $selectedTab, "selectedTabId" => $selectedTabId])
                                            @endforeach
                                        @endcan
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalBanAdd" tabindex="-1" role="dialog" aria-labelledby="Create Ban" aria-hidden="true">
                            {!! Form::open(["url" => URL::route("adm.mship.account.ban.add", $account->id)]) !!}
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">Create Ban</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            You can enter a new local ban into the system.  Doing so will cause the member to be banned across all UK services.
                                            If the member is currently connected to TeamSpeak or active on the forum, their access will be rescinded immediately.
                                        </p>
                                        <p>
                                        <div class="form-group">
                                            <label for="ban_reason_id">Ban Reason</label>
                                            <select name="ban_reason_id">
                                                @foreach($banReasons as $br)
                                                    <option value="{{ $br->id }}">{{ $br }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="ban_reason_extra">Extra Info<br /><small>This will be sent to the member.</small></label>
                                            <textarea name="ban_reason_extra" class="form-control" rows="5">{{ old("ban_reason_extra") }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="ban_note_content">Note<br /><small>This will *not* be sent to the member.</small></label>
                                            <textarea name="ban_note_content" class="form-control" rows="5">{{ old("ban_note_content") }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Confirm</button>
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endcan

                    @can('use-permission', "adm/mship/account/*/notes")
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

                                                @can('use-permission', "adm/mship/account/*/note/create")
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalNoteCreate">Add Note</button>
                                                @endcan
                                            </div>
                                        </div>

                                        <div class="clearfix">&nbsp;</div>

                                        @can('use-permission', "adm/mship/account/*/note/view")

                                            @foreach($account->notes as $note)
                                                @if((array_key_exists($note->id, Request::input("filter", [])) && count(Request::input("filter", [])) > 0) OR count(Request::input("filter", [])) < 1)
                                                    @include('adm.mship.account._note', ["note" => $note])
                                                @endif
                                            @endforeach
                                        @endcan
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalNoteFilter" tabindex="-1" role="dialog" aria-labelledby="Filter Notes" aria-hidden="true">
                            {!! Form::open(["url" => URL::route("adm.mship.account.note.filter", $account->id)]) !!}
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
                                                        <input type="checkbox" name="filter[]" value="{{ $nt->id }}" {{ Request::input("filter.".$nt->id) ? "checked='checked'" : "" }} />
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
                            {!! Form::close() !!}
                        </div>
                    @endcan

                    @can('use-permission', "adm/mship/account/*/note/create")
                        <div class="modal fade" id="modalNoteCreate" tabindex="-1" role="dialog" aria-labelledby="Create Note" aria-hidden="true">
                            {!! Form::open(array("url" => URL::route("adm.mship.account.note.create", $account->id))) !!}
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
                                            <select name="note_type_id" class="form-control">
                                                @foreach($noteTypes as $nt)
                                                <option value="{{ $nt->id }}">
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
                            {!! Form::close() !!}
                        </div>
                    @endcan

                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
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
