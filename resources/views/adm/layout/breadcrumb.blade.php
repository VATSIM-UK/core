<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ $title }}
        <small>{{ $subTitle }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a><i class="fa fa-location-arrow"></i>VATSIM UK</a></li>
        @foreach($breadcrumb as $b)
            @if($breadcrumb->last() == $b)
                <li class="active">{{ $b->get("name") }}</li>
            @elseif($b->get("uri"))
                <li><a href="{{ URL::to($b->get("uri")) }}">{{ $b->get("name") }}</a></li>
            @else
                <li>{{ $b->get("name") }}</li>
            @endif
        @endforeach
    </ol>
</section>