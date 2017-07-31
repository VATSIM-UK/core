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
                    Make a comment
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                {!! Form::open(["route" => ["adm.mship.ban.comment", $ban->id]]) !!}
                    <div class="form-group">
                        <label for="comment">Comment:</label>
                        <textarea name="comment" class="form-control" rows="5">{{ old('comment') }}</textarea>
                    </div>

                    <div class="btn-toolbar">
                        <div class="btn-group pull-left">
                            {!! link_to_route("adm.mship.account.details", "Cancel", [$ban->account_id, "bans", $ban->id], ["class" => "btn btn-info"]) !!}
                        </div>
                        <div class="btn-group pull-right">
                            {!! Form::submit("Add Comment", ["class" => "btn btn-success"]) !!}
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