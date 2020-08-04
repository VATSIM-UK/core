@extends('layout')

@section('content')

        <div class="row">

            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading">
                        <em class="fab fa-discord"></em> &thinsp; Discord Registration
                    </div>
                    <div class="panel-body">
                        <p>
                            Our community Discord server is the place to go to chat to other members of the UK Division and the wider network.
                        </p>
                        <p>
                            Registration should take you less than 60 seconds!
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-4 col-md-offset-2">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"> Step 1: Join our Discord Server
                    </div>
                    <div class="panel-body">
                        <p>
                            The first step is to join our Discord server.
                        </p>
                        <p>
                            Whether you've used Discord before or not, clicking the button below will take you to a page to either sign in or register with Discord.
                        </p>
                        <br />
                        <p>

                            @if(!$_account->discord_id)
                                <a href="{{ route('discord.invite') }}" style="text-decoration: none;" target="_blank">
                                    <button class="btn btn-primary center-block"><em class="fab fa-discord"></em> &thinsp; Join Discord Server</button>
                                </a>
                            @else
                                <a href="#" style="text-decoration: none;">
                                    <button class="btn btn-primary center-block" disabled><em class="fab fa-discord"></em> &thinsp; Already Joined</button>
                                </a>
                                <p class="text-center"><a href="{{ route('discord.destroy') }}">Unlink Account</a></p>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"> Step 2: Link Your Discord Account to VATSIM UK
                    </div>
                    <div class="panel-body">
                        <p>
                            The second step is to link your Discord account to your VATSIM UK account.
                        </p>
                        <p>
                            This allows us to assign you the relevant permissions on our Discord server.
                        </p>
                        <br />
                        <p>
                            @if(!$_account->discord_id)
                                <a href="{{ route('discord.create') }}" style="text-decoration: none;">
                                    <button class="btn btn-primary center-block"><em class="fab fa-discord"></em> &thinsp; Link Discord Account</button>
                                </a>
                            @else
                                <a href="#" style="text-decoration: none;">
                                    <button class="btn btn-primary center-block" disabled><em class="fab fa-discord"></em> &thinsp; Discord Account {{ $_account->discord_id }} Linked</button>
                                </a>
                                <p class="text-center"><a href="{{ route('discord.destroy') }}">Unlink Account</a></p>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
@stop
