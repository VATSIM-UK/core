<div class="panel panel-{{ $note->type->colour_code }} note-{{ $note->type->is_system ? "system" : "" }} note-type-{{ $note->note_type_id }}"
     id='note-{{ $note->account_note_id }}'>
    <div class="panel-heading">
        <h3 class="panel-title">
            {{ $note->type->name }}
            <span class="time pull-right">
                <small>

                    @if($note->attachment)
                        <i class="fa fa-link"></i>
                        @if($note->attachment instanceof \App\Models\Mship\Account\Ban)
                            @if($note->attachment->is_repealed)
                                *Repealed*
                            @endif
                            Ban: {!! link_to_route("adm.mship.account.details", "#".str_pad($note->attachment->account_ban_id, 5, 0, STR_PAD_LEFT), [$account->id, "notes", $note->attachment->account_ban_id]) !!}
                        @endif

                        &nbsp;&nbsp;&nbsp;
                    @endif

                    <i class="fa fa-user"></i>
                    {{ $note->writer->name }}
                    ({!! link_to_route("adm.mship.account.details", $note->writer_id, [$note->writer_id]) !!}
                    )

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