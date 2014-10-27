@extends('layout')

@section('content')
    <p>
        You are currently <strong>not</strong> logged in to our Core system.  VATSIM.net now have their own Single-Sign On system for use by divisions so you <strong>will not</strong> be presented with the standard VATSIM-UK login page.
    </p>
    <p>
        This page will redirect you automatically in <span id='timerCount'>10</span> seconds.  If you don't wish to wait, you can <?=HTML::link("/mship/auth/login", "start your login now")?>.
    </p>

    <script type='text/javascript' language='javascript'>
        $( document ).ready(function() {
            // Let's start the timer to check the redirect!
            var timeLeft = 10;
            function runTimer(){
                if(timeLeft > 0){
                    timeLeft-= 1;
                    $("#timerCount").html(timeLeft);
                    setTimeout(runTimer, 1000);
                } else {
                    window.location.replace("<?=URL::to("/mship/auth/login")?>");
                }
            }
            setTimeout(runTimer, 1000);
        });
    </script>
@stop