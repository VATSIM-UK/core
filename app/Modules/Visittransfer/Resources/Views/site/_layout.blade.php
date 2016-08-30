@extends('layout')

@section('content')
    @yield('content')
@stop

@section('scripts')
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/57bb3bfca767d83b45e79605/1aqq3gev7';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();

        @if(Auth::check())
            Tawk_API.visitor = {
                name: "{{ Auth::user()->name }} ({{ Auth::user()->id }})",
                email: "{{ Auth::user()->email }}"
            };
        @endif

            Tawk_API.onLoad = function(){
                Tawk_API.addEvent('visited-page', {
                    'FullURL'    : '{{ Request::fullUrl() }}',
                }, function(error){});
            };
    </script>
@stop