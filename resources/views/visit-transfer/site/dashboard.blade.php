@extends('visit-transfer.site._layout')

@section('content')
    <div class="row">
        <div class="hidden-xs" id="visitingBoxes">
            <div class="col-md-4 hidden-xs">
            @include('components.html.panel_open', [
                'title' => 'Visiting ATC',
                'icon' => ['type' => 'vuk', 'key' => 'letter-v']
            ])
            <!-- Content Of Panel [START] -->
                <!-- Top Row [START] -->
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <p>
                            {!! trans("application.dashboard.apply.atc.visit.info") !!}
                        </p>
                    </div>

                </div>

                <br/>

                <div class="row">
                    <div class="col-xs-12 text-center">
                        @if(!\App\Models\VisitTransfer\Facility::isPossibleToVisitAtc())
                            <button class="btn btn-danger" disabled="disabled">{{ trans("application.dashboard.apply.atc.visit.no_places") }}</button>
                        @else
                            @can("create", new \App\Models\VisitTransfer\Application)

                                @if($currentVisitApplication && $currentVisitApplication->is_in_progress && $currentVisitApplication->is_atc)
                                    <a href="{{ route('visiting.application.continue', [$currentVisitApplication->public_id]) }}">
                                        <button class="btn btn-primary" href="">{{ "X".trans('application.continue') }}</button>
                                    </a>
                                @elseif($currentTransferApplication)
                                    <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.xfer_open') }}</button>
                                @else
                                    <a href="{{ route('visiting.application.start', [\App\Models\VisitTransfer\Application::TYPE_VISIT, "atc"]) }}">
                                        <button class="btn btn-success">{{ trans('application.dashboard.apply.atc.visit.start') }}</button>
                                    </a>
                                @endif
                            @else
                                <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.atc.visit.unable')}}</button>
                            @endcan
                        @endif
                    </div>
                </div>

                @include('components.html.panel_close')
            </div>

            <div class="col-md-4 hidden-xs">
            @include('components.html.panel_open', [
                'title' => 'What about pilots?',
                'icon' => ['type' => 'vuk', 'key' => 'letter-p']
            ])
            <!-- Content Of Panel [START] -->
                <!-- Top Row [START] -->
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">
                        <p>
                            You can apply to become a pilot visitor if you:
                        <ul>
                            <li>want to train towards <strong>any pilot rating*</strong> within the UK</li>
                            <li><strong>do not</strong> wish to leave your current division</li>
                        </ul>
                        <small>*Each rating will require a separate application.</small>
                        </p>
                    </div>

                </div>

                <br/>

                <div class="row">
                    <div class="col-xs-12 text-center">
                        @if(!\App\Models\VisitTransfer\Facility::isPossibleToVisitPilot())
                            <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.pilot.visit.no_places') }}</button>
                        @else
                            @can("create", new \App\Models\VisitTransfer\Application)
                                <a href="{{ route('visiting.application.start', [\App\Models\VisitTransfer\Application::TYPE_VISIT, 'pilot']) }}">
                                    <button class="btn btn-success">{{ trans('application.dashboard.apply.pilot.visit.start') }}</button>
                                </a>
                            @elseif($currentVisitApplication && $currentVisitApplication->is_in_progress && $currentVisitApplication->is_pilot)
                                <a href="{{ route('visiting.application.continue', [$currentVisitApplication->public_id]) }}">
                                    <button class="btn btn-primary" href="">{{ trans('application.continue') }}</button>
                                </a>
                            @elseif($currentTransferApplication)
                                <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.xfer_open') }}</button>
                            @else
                                <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.pilot.visit.unable') }}</button>
                            @endcan
                        @endif
                    </div>
                </div>

                @include('components.html.panel_close')
            </div>
        </div>

        <div class="col-md-4 hidden-xs" id="transferringAtcBox">
        @include('components.html.panel_open', [
            'title' => 'Transferring ATC',
            'icon' => ['type' => 'vuk', 'key' => 'letter-t']
        ])
        <!-- Content Of Panel [START] -->
            <!-- Top Row [START] -->
            <div class="row">
                <div class="col-md-10 col-xs-offset-1">
                    <p>
                        You can apply to transfer if you:
                    <ul>
                        <li>want the freedom to <strong>control anywhere</strong> within the UK*</li>
                        <li><strong>are happy</strong> to leave your current division</li>
                    </ul>
                    <small>*subject to appropriate training and GRP restrictions.</small>
                    </p>
                </div>

            </div>

            <br/>

            <div class="row">
                <div class="col-xs-12 text-center">
                    @if(!\App\Models\VisitTransfer\Facility::isPossibleToTransfer())
                        <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.atc.transfer.no_places') }}</button>
                    @else
                        @can("create", new \App\Models\VisitTransfer\Application)
                            <a href="{{ route('visiting.application.start', [\App\Models\VisitTransfer\Application::TYPE_TRANSFER, 'atc']) }}">
                                <button class="btn btn-success" href="">{{ trans('application.dashboard.apply.atc.transfer.start') }}</button>
                            </a>
                        @elseif($currentTransferApplication)
                            <a href="{{ route('visiting.application.continue', [$currentTransferApplication->public_id]) }}">
                                <button class="btn btn-primary">{{ trans('application.continue') }}</button>
                            </a>
                        @elseif($currentVisitApplication)
                            <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.visit_open') }}</button>
                        @else
                            <button class="btn btn-danger" disabled="disabled">{{ trans('application.dashboard.apply.atc.transfer.unable') }}</button>
                        @endcan
                    @endif
                </div>
            </div>

            @include('components.html.panel_close')
        </div>

        <div class="col-xs-12 visible-xs">
            @include('components.html.panel_open', [
                'title' => 'Start a new Application',
                'icon' => ['type' => 'fa', 'key' => 'exclamation']
            ])
            <p class="text-center">You can only complete your application and references on a non-mobile device.</p>
            @include('components.html.panel_close')
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" id="applicationHistory">
            @include('components.html.panel_open', [
                'title' => 'Application History',
                'icon' => ['type' => 'fa', 'key' => 'list-alt']
            ])
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th width="col-md-1">ID</th>
                            <th width="col-md-2">Type</th>
                            <th width="col-md-3">Facility</th>
                            <th class="col-md-2 hidden-xs hidden-sm">Submitted</th>
                            <th class="col-md-1 text-center">Status</th>
                            <th class="col-md-1 text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($allApplications) < 1)
                            <tr>
                                <td colspan="6" class="text-center">You have no applications to display.</td>
                            </tr>
                        @else
                            @foreach($allApplications as $application)
                                <tr>
                                    <td>
                                        @if($application->is_in_progress)
                                            <a href="{{ route('visiting.application.continue', [$application->public_id]) }}">{{ $application->public_id }}</a>
                                        @else
                                            <a href="{{ route('visiting.application.view', [$application->public_id]) }}">{{ $application->public_id }}</a>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $application->type_string }}
                                    </td>
                                    <td>{{ $application->facility_name }}</td>
                                    <td class="hidden-xs hidden-sm">
                                        @if($application->submitted_at == null)
                                            Not yet submitted
                                        @else
                                            <span class="hidden-xs">{{ $application->submitted_at }} UTC</span>
                                            <span class="visible-xs">{{ $application->submitted_at->toFormattedDateString() }}
                                                UTC</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @include("visit-transfer.partials.application_status", ["application" => $application])
                                    </td>
                                    <td class="text-center">
                                        @if($application->is_in_progress)
                                            <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#withdrawModal">
                                                WITHDRAW
                                            </button>
                                            <div class="modal fade" role="dialog" id="withdrawModal">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            Withdraw Application
                                                        </div>
                                                        <div class="modal-body">
                                                            If you wish to withdraw your application (without penalty) you can do so by clicking the button below.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <form action="{{ route('visiting.application.withdraw.post', [$application->public_id]) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-danger">WITHDRAW APPLICATION - THIS CANNOT BE UNDONE</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>

            </div>
            @include('components.html.panel_close')
        </div>
    </div>

    @if($pendingReferences->count() > 0)
        <div class="row" id="pendingReferences">
            <div class="col-md-12">
                @include('components.html.panel_open', [
                    'title' => 'Pending References',
                    'icon' => ['type' => 'fa', 'key' => 'list-alt']
                ])
                <div class="row">
                    <div class="col-md-10 col-md-offset-1">

                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th width="col-md-2">Applicant Name (CID)</th>
                                <th width="col-md-1">Type</th>
                                <th width="col-md-2">Facility</th>
                                <th width="col-md-2" class="hidden-xs hidden-sm">Submitted</th>
                                <th width="col-md-2" class="hidden-xs hidden-sm">Reference Due By</th>
                                <th class="col-md-1 text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($pendingReferences) < 1)
                                <tr>
                                    <td colspan="6" class="text-center">You have no applications to display.</td>
                                </tr>
                            @else
                                @foreach($pendingReferences as $reference)
                                    <tr>
                                        <td>{{ $reference->application->account->name }}
                                            ({{ $reference->application->account->id }})
                                        </td>
                                        <td>{{ $reference->application->type_string }}</td>
                                        <td>{{ $reference->application->facility_name }}</td>
                                        <td class="hidden-xs hidden-sm">
                                            <span class="hidden-xs">{{ $reference->application->submitted_at }} UTC</span>
                                            <span class="visible-xs">{{ $reference->application->submitted_at->toFormattedDateString() }}
                                                UTC</span>
                                        </td>
                                        <td class="hidden-xs hidden-sm">
                                            <span class="hidden-xs">{{ $reference->application->submitted_at->addDays(10) }} UTC</span>
                                            <span class="visible-xs">{{ $reference->application->submitted_at->addDays(10)->toFormattedDateString() }}
                                                UTC</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($reference->token)
                                              <a href="{{ route('visiting.reference.complete', [$reference->token->code]) }}">Complete</a>
                                            @else
                                              <i>This reference has expired</i>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>

                </div>
                @include('components.html.panel_close')
            </div>
        </div>
    @endif
@stop

@section("scripts")
    @parent

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tour/0.11.0/js/bootstrap-tour.min.js" integrity="sha384-vzCaHnPHCvqX/NZEoFP8o6Kl3oz4t69lFsHpZ8uIzr+NURIp0PoavFo0OXXchs3V" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var tour = new Tour({
            name: "VT-Dashboard",
            steps: [
                    @if($pendingReferences->count() > 0)
                    {
                        element: "#pendingReferences",
                        title: "Pending References",
                        content: "You have pending references that require completion here.  This list will be updated automatically.",
                        backdrop: true,
                        placement: "top"
                    },
                @endif

                @if($allApplications->count() > 0)
                    {
                        element: "#applicationHistory",
                        title: "Application History",
                        content: "You can view any open, or historic, applications here.",
                        backdrop: true,
                        placement: "top"
                    },
                @endif

                @can("create", new \App\Models\VisitTransfer\Application)
                    {
                        element: "#visitingBoxes",
                        title: "Visiting the UK",
                        content: "If you wish to visit with your Controller rating or to gain a pilot rating, you should visit the UK.",
                        backdrop: true,
                        placement: "top"
                    },
                    {
                        element: "#transferringAtcBox",
                        title: "Transferring ATC",
                        content: "If you wish to transfer to the UK as a controller, start your application here.",
                        backdrop: true,
                        placement: "top"
                    },
                @endcan
            ]
        });

        tour.init();
        tour.start();
    </script>
@stop

