@extends('adm.layout')

@section('content')
<div class="row">

    @include('adm.layout.messages')

    <div class="col-md-12">
        <div class="box box-tools">
            <div class="box-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li {{ $selectedTab == "basic" ? "class='active'" : "" }}><a href="#basic" role="tab" data-toggle="tab">Basic Details</a></li>
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/roles"))
                        <li {!! $selectedTab == "roles" ? "class='active'" : "" !!}><a href="#role" role="tab" data-toggle="tab">Roles</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/feedback"))
                        <li {!! $selectedTab == "feedback" ? "class='active'" : "" !!}><a href="#feedback" role="tab" data-toggle="tab">Feedback</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/bans"))
                        <li {!! $selectedTab == "bans" ? "class='active'" : "" !!}><a href="#bans" role="tab" data-toggle="tab">Bans</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/notes"))
                        <li {!! $selectedTab == "notes" ? "class='active'" : "" !!}><a href="#notes" role="tab" data-toggle="tab">Notes</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/flags"))
                        <li {!! $selectedTab == "flags" ? "class='active'" : "" !!}><a href="#flags" role="tab" data-toggle="tab">Review Flags</a></li>
                    @endif
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/datachanges"))
                        <li {!! $selectedTab == "datachanges" ? "class='active'" : "" !!}><a href="#datachanges" role="tab" data-toggle="tab">Data Changes</a></li>
                    @endif
                </ul>
                <br />

                <div class="tab-content">
                    <div class="tab-pane fade {{ $selectedTab == "basic" ? "in active" : "" }}" id="basic">

                        <div class="col-md-12">
                            <div class="btn-toolbar">
                                <div class="btn-group pull-right">
                                    @if($_account->hasPermission("adm/mship/account/".$account->id."/impersonate"))
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
                                        <label for="id">CID:</label>
                                        {{ $account->id }}
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Name:</label>
                                        {{ $account->name }}
                                    </div>
                                    @if($_account->hasPermission("adm/mship/account/email/view"))
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

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/impersonate"))
                        <div class="modal fade" id="modalImpersonate" tabindex="-1" role="dialog" aria-labelledby="Impersonate" aria-hidden="true">
                            {!! Form::open(["url" => URL::route("adm.mship.account.impersonate", $account->id), "target" => "_blank"]) !!}
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
                                            {!! Form::Label("reason", "Reason") !!}
                                            {!! Form::textarea("reason", null, ["class" => "form-control"]) !!}
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/roles"))
                        <div class="tab-pane fade {{ $selectedTab == "roles" ? "in active" : "" }}" id="role">
                            <!-- general form elements -->
                            <div class="box box-primary">

                                    <div class="box-header">
                                        <h3 class="box-title">Roles</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">

                                        <div class="btn-toolbar">
                                            <div class="btn-group pull-right">
                                                @if($_account->hasPermission("adm/mship/account/".$account->id."/roles/attach"))
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
                                                    @if($_account->hasPermission("adm/mship/account/".$account->id."/roles/".$account->id."/detach"))
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
                                                    @if($_account->hasPermission("adm/mship/account/".$account->id."/roles/".$r->id."/detach"))
                                                        <td>{!! Form::button("Delete", ["data-href" => URL::route("adm.mship.account.role.detach", [$account->id, $r->id]), "data-toggle" => "confirmation", "class" => "btn btn-xs btn-danger"]) !!}</td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/feedback"))
                        <div class="tab-pane fade {{ $selectedTab == "feedback" ? "in active" : "" }}" id="feedback">
                            <!-- general form elements -->
                            <div class="box box-primary">

                                    <div class="box-header">
                                        <h3 class="box-title">Recieved Feedback</h3>
                                    </div><!-- /.box-header -->
                                    <div class="box-body">
                                        <table style="width:100%">
                                          <thead>
                                              <tr>
                                                  <th class="col-md-1">
                                                      ID
                                                  </th>
                                                  <th class="col-md-3">
                                                        Subject of Feedback
                                                  </th>
                                                  <th>Facility</th>
                                                  <th>Date Submitted</th>
                                                  <th>Action Taken</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              @foreach($feedback as $f)
                                              <tr>
                                                  <td>{!! link_to_route('adm.mship.feedback.view', $f->id, [$f->id]) !!}</td>
                                                  <td>{{ $f->account->real_name }}</td>
                                                  <td>{{ $f->isATC() ? "ATC" : "Pilot"  }}</td>
                                                  <td>{{ $f->created_at->format("d-m-Y H:i A") }}</td>
                                                  <td>
                                                    @if ($f->actioned_at)
                                                        {!! HTML::img("tick_mark_circle", "png", 35, 47) !!}
                                                    @else
                                                        {!! HTML::img("cross_mark_circle", "png", 35, 47) !!}
                                                    @endif
                                                  </td>
                                              </tr>
                                              @endforeach
                                          </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    @endif

                    <!-- Modals -->
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/roles/attach"))
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/bans"))
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
                                                @if($_account->hasPermission("adm/mship/account/".$account->id."/ban/add") && !$account->is_banned)
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalBanAdd">Add Ban</button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="clearfix">&nbsp;</div>

                                        @if($_account->hasPermission("adm/mship/account/".$account->id."/ban/view"))
                                            @foreach($account->bans as $ban)
                                                @include("adm.mship.account._ban", ["ban" => $ban, "selectedTab" => $selectedTab, "selectedTabId" => $selectedTabId])
                                            @endforeach
                                        @endif
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/notes"))
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
                                                @if($_account->hasPermission("adm/mship/account/".$account->id."/note/filter"))
                                                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalNoteFilter">Change Filter</button>
                                                @endif

                                                @if($_account->hasPermission("adm/mship/account/".$account->id."/note/create"))
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalNoteCreate">Add Note</button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="clearfix">&nbsp;</div>

                                        @if($_account->hasPermission("adm/mship/account/".$account->id."/note/view"))
                                            @foreach($account->notes as $note)
                                                @if((array_key_exists($note->id, Input::get("filter", [])) && count(Input::get("filter")) > 0) OR count(Input::get("filter")) < 1)
                                                    @include('adm.mship.account._note', ["note" => $note])
                                                @endif
                                            @endforeach
                                        @endif
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
                                                        <input type="checkbox" name="filter[]" value="{{ $nt->id }}" {{ Input::get("filter.".$nt->id) ? "checked='checked'" : "" }} />
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/note/create"))
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
                                            <select name="note_type_id" class="form-control selectpicker">
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
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/flags"))
                        <div class="tab-pane fade {{ $selectedTab == "flags" ? "in active" : "" }}" id="flags">Review Flags</div>
                    @endif

                    @if($_account->hasPermission("adm/mship/account/".$account->id."/datachanges"))
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
                                            @foreach($account->dataChanges as $dc)
                                            <tr>
                                                <td>{{ $dc->data_key }}</td>
                                                <td>{{ $_account->hasPermission("adm/mship/account/".$account->id."/datachanges/view") ? $dc->data_old : "[No Permission]" }}</td>
                                                <td>{{ $_account->hasPermission("adm/mship/account/".$account->id."/datachanges/view") ? $dc->data_new : "[No Permission]" }}</td>
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
    @if($_account->hasPermission("adm/mship/account/".$account->id."/timeline"))
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Recent Activities</h3>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    {{--@include('adm.sys.activity.stream', ['activities' => $account->activity_recent])--}}
                    [Not implemented]
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
