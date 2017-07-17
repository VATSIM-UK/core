@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        All Modules
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="mship-accounts" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="col-md-2">Name</th>
                                <th class="col-md-6">Description</th>
                                <th>Slug - Namespace</th>
                                <th>Version</th>
                                <th style="text-align: center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($modules as $m)
                            <tr>
                                <td>{{ $m['name'] }}</td>
                                <td>{{ $m['description'] }}</td>
                                <td>{{ $m['slug'] }} - {{ $m['basename'] }}</td>
                                <td>{{ $m['version'] }}</td>
                                <td align="center">
                                    @if($m['enabled'])
                                        {!! Button::success("Active")->extraSmall()->asLinkTo(route("adm.sys.module.disable", [$m['slug']])) !!}
                                    @else
                                        {!! Button::danger("Disabled")->extraSmall()->asLinkTo(route("adm.sys.module.enable", [$m['slug']])) !!}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('scripts')
    @parent
    {!! HTML::script('/AdminLTE/js/plugins/datatables/jquery.dataTables.js') !!}
    {!! HTML::script('/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js') !!}
@stop