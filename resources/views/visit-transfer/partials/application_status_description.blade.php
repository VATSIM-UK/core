@if($application->is_open)
    <span class="btn btn-success btn-xs text-center">{{ $application->status_string }}</span>
    <span class="hidden-xs hidden-sm">
                                                Some text.
                                            </span>
@elseif($application->requires_action)
    <span class="btn btn-warning btn-xs text-center">{{ $application->status_string }}</span>
    <span class="hidden-xs hidden-sm">
                                                Some text.
                                            </span>
@else
    <span class="btn btn-danger btn-xs text-center">{{ $application->status_string }}</span>
    <span class="hidden-xs hidden-sm">
                                                Some text.
                                            </span>
@endif