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
                    Repeal Details
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <p>
                    Repealing this ban will remove it from the member's record.  It will still be visible, but will not count towards any statistics regarding their membership bans.
                    This action is to be treated the same as deleting a ban.
                </p>

                <p>
                    It is very important you complete this form properly, as the member <strong>will</strong> be notified instantly that their ban has been repealed and it will not be
                    possible to amend your comments.
                </p>

                {!! Form::open(["route" => ["adm.mship.ban.repeal", $ban->id]]) !!}
                    <div class="form-group">
                        <label for="reason">Reason for repeal</label>
                        <textarea name="reason" class="form-control" rows="5">{{ old('reason') }}</textarea>
                    </div>

                    <div class="btn-toolbar">
                        <div class="btn-group pull-left">
                            {!! link_to_route("adm.mship.account.details", "Cancel", [$ban->account_id, "bans", $ban->id], ["class" => "btn btn-danger"]) !!}
                        </div>
                        <div class="btn-group pull-right">
                            {!! Form::submit("Repeal Ban (Cannot be undone)", ["class" => "btn btn-danger"]) !!}
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
@stop