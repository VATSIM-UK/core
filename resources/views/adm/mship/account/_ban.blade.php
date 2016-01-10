<div class="panel panel-danger" id='ban-{{ $ban->account_ban_id }}'>
    <div class="panel-heading">
        <h3 class="panel-title">
            {!! $ban->type_string !!} - {!! $ban->period_amount !!} {!! $ban->period_unit_string !!}
            <span class="time pull-right">
                <small>
                    <i class="fa fa-user"></i>
                    {{ $ban->banner->name }}
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

        <div class="btn-toolbar">
            <div class="btn-group pull-right">
                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/ban/edit") && $ban->is_active)
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalBanDurationEdit">Change Ban Duration</button>
                @endif

                @if($_account->hasPermission("adm/mship/account/".$account->account_id."/ban/reverse"))
                    {!! link_to_route("adm.mship.ban.repeal", "Repeal Ban", [$ban->account_ban_id], ["class" => "btn btn-danger"]) !!}
                @endif
            </div>
        </div>

        <div class="clearfix">&nbsp;</div>

        <p>
            <strong>Ban Start:</strong> {{ $ban->period_start->diffForHumans() }}
            , {{ $ban->period_start->toDateTimeString() }}<br/>
            <strong>Ban Finish:</strong> {{ $ban->period_finish->diffForHumans() }}
            , {{ $ban->period_finish->toDateTimeString() }}
        </p>

        <p>
            <strong>Reason</strong>
            <br/>
            <em>
                {{ $ban->reason}}
                @if($ban->reason_extra)
                    {{ $ban->reason_extra }}
                @endif
            </em>
        </p>
        @if(count($ban->notes) > 0)
            <strong>
                Related Notes (Newest first) -
                <a data-toggle="collapse" href="#banNotes{{ $ban->account_ban_id }}" aria-expanded="{{ (isset($selectedTab) && $selectedTab == "bans" && $selectedTabId == $ban->account_ban_id) ? true : false }}"
                   aria-controls="#banNotes{{ $ban->account_ban_id }}">Toggle Display</a>
            </strong>

            <div class="{{ (isset($selectedTab) && $selectedTab == "bans" && $selectedTabId == $ban->account_ban_id) ? "" : "collapse" }}" id="banNotes{{ $ban->account_ban_id }}">
                @foreach($ban->notes->sortByDesc("created_at") as $note)
                    @include('adm.mship.account._note', ["note" => $note])
                @endforeach
            </div>
        @endif
    </div>
</div>