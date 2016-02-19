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

                <div class="col-md-8 col-md-offset-2 text-center">
                    {!! ButtonGroup::withContents([
                        Button::success("YES, please!")->large()->submit(),
                        Button::danger("NO, thanks.")->large()->submit()
                    ]) !!}
                </div>
            </div>

        {!! HTML::panelClose() !!}
    </div>
@stop