<div class="panel panel-{{ $ban->is_repealed ? "info" : "danger" }}" id='ban-{{ $ban->id }}'>
    <div class="panel-heading">
        <h3 class="panel-title">
            {!! $ban->type_string !!} - {!! $ban->period_amount_string !!}
            @if($ban->is_repealed)
                **REPEALED**
            @endif
            <span class="time pull-right">
                <small>
                    <i class="fa fa-user"></i>
                    {{ !is_null($ban->banner) ? $ban->banner->name : 'Unknown' }}
                    ({!! link_to_route("adm.mship.account.details", $ban->banned_by, [$ban->banned_by]) !!}
                    )

                    &nbsp;&nbsp;&nbsp;&nbsp;

                    <i class="fa fa-clock-o"></i>
                    {{ $ban->created_at->diffForHumans() }}
                    , {{ $ban->created_at->toDateTimeString() }}
                </small>
            </span>
        </h3>
    </div>
    <div class="panel-body">

        @if($ban->is_local)
            <div class="btn-toolbar">
                <div class="btn-group pull-right">
                    @if($_account->hasPermission("adm/mship/ban/".$ban->id."/repeal") && !$ban->is_repealed)
                        {!! link_to_route("adm.mship.ban.repeal", "Repeal Ban", [$ban->id], ["class" => "btn btn-danger"]) !!}
                    @endif
                </div>

                <div class="btn-group pull-right">
                    @if($_account->hasPermission("adm/mship/ban/".$ban->id."/modify") && $ban->is_active)
                        {!! link_to_route("adm.mship.ban.modify", "Modify Ban", [$ban->id], ["class" => "btn btn-warning"]) !!}
                    @endif
                </div>

                <div class="btn-group pull-right">
                    @if($_account->hasPermission("adm/mship/account/".$account->id."/note/create") && !$ban->is_repealed)
                        {!! link_to_route("adm.mship.ban.comment", "Attach Note", [$ban->id], ["class" => "btn btn-info"]) !!}
                    @endif
                </div>
            </div>
        @endif

        <div class="clearfix">&nbsp;</div>

        <p>
            <strong>Ban Start:</strong> {{ $ban->period_start->diffForHumans() }}
            , {{ $ban->period_start->toDateTimeString() }}<br/>
            <strong>Ban Finish:</strong>
            @if($ban->period_finish != NULL)
                {{ $ban->period_finish->diffForHumans() }}
                , {{ $ban->period_finish->toDateTimeString() }}
            @else
                @if($ban->is_local)
                    Forever
                @elseif($ban->is_network)
                    Unknown
                @endif
            @endif
        </p>

        <p>
            <strong>Reason</strong>
            <br/>
            <em>
                {{ $ban->reason}}<br />
                @if($ban->reason_extra)
                    {!! $ban->reason_extra !!}
                @endif
            </em>
        </p>
        @if(count($ban->notes) > 0)
            <strong>
                Related Notes (Newest first) -
                <a data-toggle="collapse" href="#banNotes{{ $ban->id }}" aria-expanded="{{ (isset($selectedTab) && $selectedTab == "bans" && $selectedTabId == $ban->id) ? true : false }}"
                   aria-controls="#banNotes{{ $ban->id }}">Toggle Display</a>
            </strong>

            <div class="{{ (isset($selectedTab) && $selectedTab == "bans" && $selectedTabId == $ban->id) ? "" : "collapse" }}" id="banNotes{{ $ban->id }}">
                @foreach($ban->notes->sortByDesc("created_at") as $note)
                    @include('adm.mship.account._note', ["note" => $note])
                @endforeach
            </div>
        @endif
    </div>
</div>
