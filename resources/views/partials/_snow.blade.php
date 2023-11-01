@if(Carbon\Carbon::now()->month == 12 || Carbon\Carbon::now()->dayOfYear < 10)
    @vite('resources/assets/js/snow.js')
@endif
