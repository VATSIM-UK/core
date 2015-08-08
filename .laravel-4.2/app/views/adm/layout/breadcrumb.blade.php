<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ $title }}
        <small>{{ $subTitle }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ URL::route("adm.dashboard") }}"><i class="fa fa-location-arrow"></i>VATSIM UK</a></li>
        <li><a href="{{ URL::route("adm.dashboard") }}">Admin CP</a></li>
        @foreach($breadcrumb as $b)
            @if(last($breadcrumb) == $b)
                <li class="active">{{ ucfirst($b[0]) }}</li>
            @elseif($b[2])
                <li><a href="{{ URL::to($b[1]) }}">{{ ucfirst($b[0]) }}</a></li>
            @else
                <li>{{ ucfirst($b[0]) }}</li>
            @endif
        @endforeach
    </ol>
</section>