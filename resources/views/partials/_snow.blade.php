@if(Carbon\Carbon::now()->month == 12 || Carbon\Carbon::now()->dayOfYear < 10)
    <script src="{{ mix('js/snow.js') }}"></script>
@endif
