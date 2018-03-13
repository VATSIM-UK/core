@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Endorsements
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table class="table">
                        <thead>
                            <th>Endorsement</th>
                            <th></th>
                        </thead>
                        <tbody>
                            @foreach($endorsements as $endorsement)
                            <tr>
                                <td>{{ $endorsement }}</td>
                                <td>
                                    {{ Form::open(['method' => 'POST', 'route' => ['adm.atc.endorsement.index.post']]) }}
                                    <div class="input-group">
                                        {{ Form::hidden('endorsement', $endorsement) }}
                                        {{ Form::text('cid', null, ['class' => 'form-control', 'placeholder' => "Enter the user's CID here"]) }}
                                        <span class="input-group-btn">
                                            {{ Form::submit('Go', ['class' => "btn"]) }}
                                        </span>
                                    </div><!-- /input-group -->
                                    {{ Form::close() }}
                                </td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@endsection

