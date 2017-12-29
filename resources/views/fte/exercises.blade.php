@extends ('layout')

@section('content')
    @foreach($exercises as $exercise)
        @if($loop->first || $loop->index % 3 == 0)
            <div class="row">
                @endif
                <div class="col-md-4">
                    <div class="panel panel-ukblue">
                        <div class="panel-heading"><i class="glyphicon glyphicon-triangle-right"></i>&thinsp; {{ $exercise->name }}</div>
                        <div class="panel-body">
                            @if($exercise->image)
                                <img src="{{ $exercise->image->asset() }}" width="100%" height="100px" alt="{{ $exercise->name }}">
                            @endif
                            <p style="margin-top: 10px;">{{ $exercise->description }}</p>
                            <div class="text-right">
                                <a href="{{ route('fte.exercises', $exercise) }}" class="btn btn-primary">View Details &gt;&gt;</a>
                            </div>
                        </div>
                    </div>
                </div>
                @if($loop->last || $loop->index % 3 == 2)
            </div>
        @endif
    @endforeach
@stop
