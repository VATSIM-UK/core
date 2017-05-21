@extends('layout')

@section('content')
    @yield('content')
@stop

@section('scripts')
    <script type="text/javascript">
        Tawk_API.onLoad = function(){
            @if(Auth::check() && Auth::user()->hasOpenVisitingTransferApplication())
                  Tawk_API.setAttributes({
                'id'    : '{{ Auth::user()->visit_transfer_current->id }}',
                'publicid' : '{{ Auth::user()->visit_transfer_current->public_id }}'
            }, function(error){});
            @endif
        };
    </script>
@stop