@extends('adm.layout')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header">
                <div class="box-title">Ban Details</div>
            </div>
            <div class="box-body">
                @include("adm.mship.account._ban", ["ban" => $ban, "account" => $ban->account])
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Modification Details
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <p>
                    Please ensure that this page is completed correctly as it <strong>will</strong> notify the member automatically.  The reason you provide on this page <strong>will</strong>
                     be sent in the email, but the note will <strong>not</strong>.
                </p>
                {!! Form::open(["route" => ["adm.mship.ban.modify", $ban->id]]) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="finish_date">Finish Date:</label>
                                <input type="text" class="form-control datepicker" name="finish_date" value="{{ old('finish_date', $ban->period_finish->toDateString()) }}" />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="finish_time">Finish Time:</label>
                                <div class="input-group bootstrap-timepicker timepicker">
                                    <input id="finish_time" name="finish_time" type="text" class="form-control input-small">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="form-group">
                        <label for="reason_extra">Reason:<br /><small>The member *will* be sent this information.</small></label>
                        <textarea name="reason_extra" class="form-control" rows="5">{{ old('reason_extra') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="note">Note:<br /><small>The member will *not* be sent this information.</small></label>
                        <textarea name="note" class="form-control" rows="5">{{ old('note') }}</textarea>
                    </div>

                    <div class="btn-toolbar">
                        <div class="btn-group pull-left">
                            {!! link_to_route("adm.mship.account.details", "Cancel", [$ban->account_id, "bans", $ban->id], ["class" => "btn btn-info"]) !!}
                        </div>
                        <div class="btn-group pull-right">
                            {!! Form::submit("Modify Ban", ["class" => "btn btn-danger"]) !!}
                        </div>
                    </div>
                {!! Form::close() !!}
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
@stop

@section('scripts')
@parent
{!! HTML::script('/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js') !!}

<script language="javascript" type="text/javascript">
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        startDate: 'today',
        autoclose: true,
        defaultViewDate: {
            year: "{{ $ban->period_start->year }}",
            month: "{{ $ban->period_start->month }}",
            day: "{{ $ban->period_start->day }}"
        }
    });
    $('#finish_time').timepicker({
        defaultTime: '{{ $ban->period_start->toTimeString() }}',
        showSeconds: false,
        template: 'dropdown',
        explicitMode: true,
        showMeridian: false,
    });
</script>
@stop