@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        @if(count($members) > 0)
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Members ({{ count($members) }})
                        @if(count($members) >= 20)
                        <br />
                        <em>Large number of search results - try refining your criteria.</em>
                        @endif
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="mship-accounts" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>ATC Rating</th>
                                <th>Pilot Rating</th>
                                <th>State</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $m)
                            <tr>
                                <td>{!! link_to_route("adm.mship.account.details", $m->id, [$m->id]) !!}</td>
                                <td>{{ $m->name }}</td>
                                <td>{{ $_account->hasPermission("adm/mship/account/email/view") ? $m->email : "[ No Permission ]" }}</td>
                                <td>{{ $m->qualification_atc }}</td>
                                <td>{{ $m->qualification_pilot }}</td>
                                <td>{{ $m->primary_state->name }}</td>
                                <td>{!! $m->status_string == "Active" ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">'.$m->status_string.'</span>' !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        @endif

        @if(count($emails) > 0)
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Member Emails ({{ count($emails) }})
                        @if(count($emails) >= 20)
                        <br />
                        <em>Large number of search results - try refining your criteria.</em>
                        @endif
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="mship-accounts" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Created</th>
                                <th>Verified</th>
                                <th>Deleted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emails as $e)
                            <tr>
                                <td>{!! link_to_route('adm.mship.account.details', $e->id, [$e->id]) !!}</td>
                                <td>{{ $e->account->name }}</td>
                                <td>{{ $_account->hasPermission("adm/mship/account/email/view") ? $e->email : "[ No Permission ]" }}</td>
                                <td>{{ $e->created_at }}</td>
                                <td>{{ $e->verified_at }}</td>
                                <td>{{ $e->deleted_at ? $e->deleted_at : "Not Deleted" }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        @endif
    </div>
</div>
@stop

@section('styles')
@parent
{!! HTML::style('/assets/css/datatables/jquery.dataTables.modified.css') !!}
@stop

@section('scripts')
@parent
{!! HTML::script('/assets/js/plugins/datatables/jquery.dataTables.min.js') !!}
{!! HTML::script('/assets/js/plugins/datatables/dataTables.bootstrap.js') !!}
<script type="text/javascript">
  $(document).ready(function(){
    $('#mship-accounts').DataTable({
      "paging": false,
      "searching": false,
      "info": false,
      "ordering": true,
    });
  });
</script>
@stop
