@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        All Local Bans
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="mship-roles" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>User Banned</th>
                            <th>Banned By</th>
                            <th>Date Issued</th>
                            <th>Starts</th>
                            <th>Ends</th>
                            <th>Type</th>
                            <th>Status</th>
                            @can('use-permission', 'adm/mship/account/*/note/create')
                                <th>Note</th>
                            @endcan
                            @can('use-permission', 'adm/mship/ban/*/modify')
                                <th>Modify</th>
                            @endcan
                            @can('use-permission', 'adm/mship/ban/*/repeal')
                                <th>Repeal</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($bans as $b)
                            <tr>
                                <td>
                                    {!! link_to_route('adm.mship.account.details', $b->account->name, [$b->account]) !!}
                                </td>
                                <td>
                                    {!! link_to_route('adm.mship.account.details', $b->banner, [$b->banned_by]) !!}
                                </td>
                                <td>
                                    {{ $b->created_at->format('dS M Y') }}
                                </td>
                                <td>
                                    {{ $b->period_start->format('dS M Y') }}
                                </td>
                                <td>
                                    {{ $b->period_finish->format('dS M Y') }}
                                </td>
                                <td>
                                    @if($b->is_network)
                                        Network
                                    @elseif($b->is_local)
                                        Local
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                <td>
                                    @if($b->is_repealed)
                                        Repealed
                                    @elseif($b->is_active)
                                        Active
                                    @elseif($b->is_expired)
                                        Expired
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                @can('use-permission', 'adm/mship/account/*/note/create')
                                    <td>
                                        <div class="btn-group">
                                            @if(!$b->is_repealed)
                                                {!! link_to_route('adm.mship.ban.comment', 'Attach Note', [$b->id], ['class' => 'btn btn-info']) !!}
                                            @else
                                                <button class="btn btn-info disabled">Attach Note</button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                @can('use-permission', 'adm/mship/ban/*/modify')
                                    <td>
                                        <div class="btn-group">
                                            @if($b->is_active)
                                                {!! link_to_route('adm.mship.ban.modify', 'Modify Ban', [$b->id], ['class' => 'btn btn-warning']) !!}
                                            @else
                                                <button class="btn btn-warning disabled">Modify Ban</button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                @can('use-permission', 'adm/mship/ban/*/repeal')
                                    <td>
                                        <div class="'btn-group">
                                            @if(!$b->is_repealed)
                                                {!! link_to_route('adm.mship.ban.repeal', 'Repeal Ban', [$b->id], ['class' => 'btn btn-danger']) !!}
                                            @else
                                                <button class="btn btn-danger disabled">Repeal Ban</button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        @if(count($bans) < 1)
                            <tr>
                                <td colspan="10" align="center">No bans to display :(</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <span style="display: flex; justify-content: center;">{{ $bans->links() }}</span>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('scripts')
    @parent
    <script src='/AdminLTE/js/plugins/datatables/jquery.dataTables.js'></script>
    <script src='/AdminLTE/js/plugins/datatables/dataTables.bootstrap.js'></script>
@stop
