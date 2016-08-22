@if($application->is_open || $application->is_accepted)
    <span class="btn btn-success btn-xs text-center">{{ $application->status_string }}</span>
@elseif($application->requires_action)
    <span class="btn btn-warning btn-xs text-center">{{ $application->status_string }}</span>
@else
    <span class="btn btn-danger btn-xs text-center">{{ $application->status_string }}</span>
@endif