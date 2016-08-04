@extends('layout')

@section('title')
    Account Logout
@stop

@section('content')
    <div class="col-md-8 col-md-offset-2">
        {!! HTML::panelOpen("Logout") !!}

            <div class="row">
                <div class="col-md-12">
                    <p>
                        You have now been logged out of the <?= Input::get('ssoKey', 'X') ?> system.  Would you like for your SSO session to be terminated, preventing future access to other services?
                    </p>
                </div>

                <form class="form-horizontal" method="POST" action="{{ URL::route("mship.auth.logout.post") }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="col-md-8 col-md-offset-2 text-center">
                        {!! ButtonGroup::links([
                                Button::success("YES, please!")->addAttributes(['name' => 'processlogout', 'value' => '1'])->large()->submit(),
                                Button::danger("NO, thanks.")->addAttributes(['name' => 'processlogout', 'value' => '0'])->large()->submit()
                            ])
                        !!}
                    </div>
                </form>
            </div>

        {!! HTML::panelClose() !!}
    </div>
@stop
