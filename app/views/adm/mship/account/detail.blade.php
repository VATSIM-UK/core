@extends('adm.layout')

@section('content')
<div class="row">

    @include('adm.layout.messages')

    <div class="col-md-12">
        <div class="box box-tools">
            <div class="box-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li {{ Input::get("tab", "basic") == "basic" ? "class='active'" : "" }}><a href="#basic" role="tab" data-toggle="tab">Basic Details</a></li>
                    <li {{ Input::get("tab", "") == "notes" ? "class='active'" : "" }}><a href="#notes_info" role="tab" data-toggle="tab">Add Note/Information</a></li>
                    <li {{ Input::get("tab", "") == "flags" ? "class='active'" : "" }}><a href="#flags" role="tab" data-toggle="tab">Review Flags</a></li>
                </ul>
                <br />

                <div class="tab-content">
                    <div class="tab-pane fade {{ Input::get("tab", "basic") == "basic" ? "in active" : "" }}" id="basic">
                        <form role="form" method="POST" action="{{ URL::to('/adm/mship/account/'.$account->account_id) }}">
                            <div class="col-md-6">
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

                            <div class="col-md-6">
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
                                                    <td>{{ $q->created_at }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                                </div><!-- /.box -->
                            </div>

                            <div class='col-md-12'>
                                <div class="box box-danger">
                                    <div class="box-footer clearfix">
                                        <div class="btn-toolbar pull-right">
                                            <div class="btn-group">
                                                <button type="submit" class="btn btn-lg btn-danger">Delete</button>
                                            </div>
                                            <div class="btn-group">
                                                <button type="submit" class="btn btn-lg btn-primary">Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade {{ Input::get("tab", "") == "notes" ? "in active" : "" }}" id="notes_info">Add Note/Information</div>
                    <div class="tab-pane fade {{ Input::get("tab", "") == "flags" ? "in active" : "" }}" id="flags">Review Flags</div>
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
                @include('adm.system.timeline.widget', array('entries' => $account->timeline_entries_recent))
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
@stop