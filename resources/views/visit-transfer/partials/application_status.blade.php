@if($application->is_open || $application->is_accepted)
    <span class="label label-success label-s text-center">{{ $application->status_string }}</span>
@elseif($application->requires_action)
    <span class="label label-warning label-s text-center">{{ $application->status_string }}</span>
@else
    <span class="label label-danger label-s text-center">{{ $application->status_string }}</span>
@endif