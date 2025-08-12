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

            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-ukblue">
                    <div class="panel-heading">
                        <em class="fa fa-cog"></em> &thinsp; Link Your Discord Account to VATSIM UK
                    </div>
                    <div class="panel-body">
                        <p>
                            Clicking the button below will take you to Discord.<br>
                            There, you will need to create an account or login to an existing one.
                        </p>
                        <p>
                            Once logged in, you will be asked to click "Authorize" and give VATSIM UK permission to add you to our Discord server and assign you the relevant permissions.
                        </p>
                        <br />
                        <p>
                            @if(!$_account->discord_id)
                                <a href="{{ route('discord.create') }}" style="text-decoration: none;">
                                    <button class="btn btn-primary center-block"><em class="fab fa-discord"></em> &thinsp; Link Discord Account</button>
                                </a>
                            @else
                                <a href="#" style="text-decoration: none;">
                                <button class="btn btn-primary center-block" disabled><em class="fab fa-discord"></em>
                                        Currently registered with Discord account {{ $_account->discord_user ? '@' . $_account->discord_user['username'] : $_account->discord_id }}.
                                    </button>
                                </a>
                                <p class="text-center"><a href="{{ route('discord.destroy') }}">Unlink Account</a></p>
                            @endif
                        </p>
                    </div>
            </div>
@stop
