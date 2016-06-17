@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Failed Jobs</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <form style="display: inline-block; margin-bottom: 5px;" action="{{route('adm.sys.jobs.failed.retry', ['id' => 'all'])}}" method="POST" onsubmit="return confirm('Are you sure you want to bulk retry jobs?');">
                        {{ csrf_field() }}
                        @if (Request::has('filter_query'))
                            <input type="hidden" name="filter_query" value="{{Request::get('filter_query')}}">
                            <button name="submit" type="submit" class="btn btn-sm btn-primary">Retry Filtered</button>
                        @else
                            <button name="submit" type="submit" class="btn btn-sm btn-primary">Retry All</button>
                        @endif
                    </form>
                    <form style="display: inline-block; margin-bottom: 5px;" action="{{route('adm.sys.jobs.failed.delete', ['id' => 'all'])}}" method="POST" onsubmit="return confirm('Are you sure you want to bulk delete jobs?');">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        @if (Request::has('filter_query'))
                            <input type="hidden" name="filter_query" value="{{Request::get('filter_query')}}">
                            <button name="submit" type="submit" class="btn btn-sm btn-danger">Delete Filtered</button>
                        @else
                            <button name="submit" type="submit" class="btn btn-sm btn-danger">Delete All</button>
                        @endif
                    </form>
                    <form method="GET" action="{{ route('adm.sys.jobs.failed') }}" accept-charset="UTF-8" class="col-xs-5 col-md-3 pull-right">
                        <div class="input-group">
                            <input type="text" name="filter_query" class="form-control" placeholder="Filter Payload" value="{{Request::input('filter_query')}}">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-flat"><i class="fa fa-filter"></i></button>
                            </span>
                        </div>
                    </form>
                    <div class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered table-hover dataTable" role="grid"
                                       aria-describedby="example2_info">
                                    <thead>
                                    <tr role="row">
                                        <th>ID</th>
                                        <th>Connection</th>
                                        <th>Queue</th>
                                        <th>Payload (Job)</th>
                                        <th>Payload (Command) - Click to Expand</th>
                                        <th>Failed at</th>
                                        <th>Controls</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($jobs as $job)
                                    <tr role="row">
                                        <td>{{$job->id}}</td>
                                        <td>{{$job->connection}}</td>
                                        <td>{{$job->queue}}</td>
                                        <td>{{$job->job}}</td>
                                        <td><div style="cursor: pointer; height: 1.42857143em; overflow: hidden;" onclick="this.style.height == 'auto' ? this.style.height = '1.42857143em' : this.style.height = 'auto'">{!!$job->data['command']!!}</div></td>
                                        <td>{{$job->failed_at}}</td>
                                        <td>
                                            <form action="{{route('adm.sys.jobs.failed.retry', ['id' => $job->id])}}" method="POST" onsubmit="return confirm('Are you sure you want to retry job {{$job->id}}?');">
                                                {{ csrf_field() }}
                                                <button name="submit" type="submit" class="btn btn-xs btn-primary">Retry</button>
                                            </form>
                                            <form action="{{route('adm.sys.jobs.failed.delete', ['id' => $job->id])}}" method="POST" onsubmit="return confirm('Are you sure you want to delete job {{$job->id}}?');">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
                                                <button name="submit" type="submit" class="btn btn-xs btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">Showing
                                    {{min($jobs->perPage()*($jobs->currentPage()-1)+1, $jobs->total())}} to {{min($jobs->perPage()*$jobs->currentPage(), $jobs->count())}} of {{$jobs->total()}} entries
                                </div>
                            </div>
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    <ul class="pagination">
                                        {!! $jobs->render() !!}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop