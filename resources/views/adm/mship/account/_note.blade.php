<div class="panel panel-{{ $note->type->colour_code }} note-{{ $note->type->is_system ? "system" : "" }} note-type-{{ $note->id }}"
     id='note-{{ $note->id }}'>
    <div class="panel-heading">
        <h3 class="panel-title">
            {{ $note->type->name }}
            <span class="time pull-right">
                <small>

                    @if($note->attachment)
                        <i class="fa fa-link"></i>
                        @if($note->attachment instanceof \App\Models\VisitTransfer\Reference)
                            VT Reference: <a href="{{ route('adm.visiting.reference.view', [$note->attachment->id]) }}">#{{ str_pad($note->attachment->id, 5, 0, STR_PAD_LEFT) }}</a>
                        @elseif($note->attachment instanceof \App\Models\VisitTransfer\Application)
                            VT Application: <a href="{{ route('adm.visiting.application.view', [$note->attachment->id]) }}">#{{ str_pad($note->attachment->id, 5, 0, STR_PAD_LEFT) }}</a>
                        @endif

                        &nbsp;&nbsp;&nbsp;
                    @endif
                    <i class="fa fa-user"></i>

                    @if (is_null($note->writer))
                      Unknown/System
                    @else
                      {{$note->writer->name}}
                      {{$note->writer_id}}
                    @endif


                    &nbsp;&nbsp;&nbsp;

                    <i class="fa fa-clock-o"></i>
                    {{ $note->created_at->diffForHumans() }}
                    , {{ $note->created_at->toDateTimeString() }}
                </small>
            </span>
        </h3>
    </div>
    <div class="panel-body">
        {!! nl2br($note->content) !!}
    </div>
</div>
