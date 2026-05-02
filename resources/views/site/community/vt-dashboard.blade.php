@extends('layout')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="text-center">
            <div class="col-xs-12 visible-xs">
                <p class="alert alert-warning">You can only complete an application on a non-mobile device.</p>
        </div>
            <h1 class=""><i class="fa fa-globe"></i> &thinsp;Visiting & Transferring</h1>
            <p class="lead">
                            This page is ONLY for members that wish to transfer to or visit the United Kingdom. &nbsp;If
                            you
                            wish to transfer or visit another region/division, you should apply directly to the <a
                                    href="https://vatsim.net/docs/about/regions" rel="external nofollow" target="_blank">team at
                                the
                                relevant region/division</a>&nbsp;you wish to go to.
                        </p>
    
    <div class="alert alert-danger">
		<h3 style="margin-top: 0">P1, P2, P3, S3 and C1 Visiting Closed</h3>
		<p>We are currently <strong>not accepting applications</strong> for these due to incredibly high demand leading to long wait times.</p>
        <p><strong>Applications to transfer, to visit Shanwick and to visit for The Flying Program (TFP) are unaffected.</strong></p>
	    </div>
    </div>
</div>

    <div class="col-md-9">

        <div class="row">
            
            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-question"></i> What can I expect?
                    </div>
                    <div class="panel-body">
                        <p class="text-decoration-underline">
                            <u><strong>ATC Transfer/Visiting</strong></u>
                        </p>
                        <p>
                            <strong>If you hold the rating of S1 (Transfer)</strong>
                        </p>
                        <p>You will be treated as if you have no rating at all. You will complete the entire OBS/S1 training from the start, first waiting for a seminar with available capacity, followed by completing mentoring sessions and 
                            lastly followed by a practical exam, which you must pass in order to complete your transfer. <a href="{{ route('site.atc.newController') }}" target="_blank">You can view our New Controller page here.</a> 
                            After applying, you will be placed on the waiting list, you do not need to carry out any additional steps to join one. Expect a long wait period.</p>
                        <p>
                            <strong>If you hold the rating of S2+ (Transfer) and S3+ (Visiting)</strong>
                        </p>
                        <p><strong>No training will be provided.</strong> Upon being accepted, you will be given access to our theory resources to complete some self study and placed on the waiting list for a practical validation. 
                        When a place is available, we will require evidence of sufficient self study being completed before granting you solo validations to control positions relevant to your rating, 
                        You will be able to complete 10 hours of solo time before being put forward for a validation attempt.</p>
                        <p>It is entirely up to you to manage your own study schedule and complete the required elements within the time period provided.</p>
                        <p>Expect a short wait period for transfers and a long wait period for visiting applications.</p>
                        <p class="text-decoration-underline">
                            <u><strong>Pilot Visiting</strong></u>
                        </p>
                        <p>You will be placed on the waiting list. When a spot is available you will receive full training inline with home members.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-plane"></i> Visiting Pilots
                    </div>
                    <div class="panel-body">
                        <p>
                            The United Kingdom Division is a well respected Authorised Training Organization providing
                            some of the highest standards of training. Our mentors and examiners excel in training
                            students to a high standard.
                        </p>
                        <p>We offer a range of pilot training opportunities including gaining your pilot rating and our "The Flying Program (TFP)" which helps members learn the vital skills required to fly on the network.</p>
                        <p>
                            You should apply to become a pilot visitor if you:
                        <ul>
                            <li>want to train towards <strong>any pilot rating</strong> within the UK</li>
                            <li><strong>do not</strong> wish to leave your current division</li>
                        </ul>

                        <p class="text-center">
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
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-headset"></i> Visiting Controllers
                    </div>
                    <div class="panel-body">
                        <p>
                            <strong>If you hold the rating of S1 or S2</strong>
                        </p>
                        <p>
                            VATSIM UK <strong>does not accept</strong> visiting applications from S1 or S2 rated controllers as per the Global Transfer and Visiting Controller Policy. You are welcome to apply for a full transfer.</p>

                        <p>
                            Visiting controllers should select the facility related to their rating. C1 Visitors may also apply in a separate application for the Oceanic endorsement by submitting a ticket to the Visit/Transfer option within&nbsp;our helpdesk.&nbsp;
                        </p>
                        <p>
                            <strong>If you hold the rating of S3 or higher</strong>
                        </p>
                        <p>
                            You should apply to become a visitor if you:
                        <ul>
                            <li>want to control within the UK</li>
                            <li><strong>do not</strong> wish to leave your current division</li>
                        </ul>
                        </p>
                        <p class="text-center">
                            @if(!\App\Models\VisitTransfer\Facility::isPossibleToVisitAtc())
                                <button class="btn btn-danger" disabled="disabled">{{ trans("application.dashboard.apply.atc.visit.no_places") }}</button>
                            @elseif(in_array(Auth::user()->qualification_atc, ["OBS", "S1", "S2"]))
                                <button class="btn btn-danger" disabled="disabled">
                                    Only S3+ members can apply to visit as ATC
                                </button>
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

                </p>
                </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-headset"></i> Transferring Controllers
                    </div>
                    <div class="panel-body">

                        <p>The route to transferring to the United Kingdom depends on the rating that you currently
                            hold.</p>

                        <strong>If you hold the rating of OBS</strong>
                        <p>
                            OBS Rated members can transfer between divisions freely. You do not need to create an application and should not use this system, 
                            instead <a href="https://helpdesk.vatsim.uk/" rel="external nofollow" target="_blank">raise a ticket to
                            the Visit/Transfer option within&nbsp;our helpdesk&nbsp;</a>
                        </p>

                        <strong>If you hold the rating of S1 or higher</strong>
                        <p>
                            Please select the facility related to your current rating.
                        </p>

                        <p>
                        You should apply to transfer if you:
                        <ul>
                            <li>want the freedom to <strong>control anywhere</strong> within the UK</li>
                            <li>want to train towards <strong>any rating</strong> within the UK</li>
                            <li>are happy to <strong>leave your current division</strong></li>
                        </ul>
                        </p>
                    <p class="text-center">
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
                    </p>

                    </div>
                </div>
            </div>
            
        </div>
    

    </div>

    <div class="col-md-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading">
                        <i class="fa fa-exclamation"></i> &thinsp; Requirements
                    </div>
                    <div class="panel-body">
                        <p>
                            <strong>Please note:</strong>
                        </p>

                        <ul>
                            <li>
                                All Transferring/Visiting controllers are subject to the global&nbsp;<a
                                        href="https://vatsim.net/docs/policy/transfer-and-visiting-controller-policy/"
                                        rel="external nofollow" target="_blank">VATSIM&nbsp;Transfer &amp; Visiting
                                    Controller Policy</a>&nbsp;and <a
                                        href="https://www.vatsim.uk/policy/division-policy"
                                        rel="">VATSIM
                                    UK Division
                                    Policy</a>;
                            </li>
                            <li>
                                Only permanent controller ratings are relevant for visiting/transferring ATC
                                applications
                                (<strong>non permanent ratings of SUP/ADM/I1/I3 are not relevant</strong>);
                            </li>
                        </ul>
                        

                    </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="fa fa-list"></i> &thinsp; Visiting Endorsements (ATC)
            </div>
            <div class="panel-body">
                <p>
                    <strong>Approach Rating Endorsement</strong>
                </p>

                <p>
                    All UK APP + ADC positions with the exception of those listed in the ATC Training Policy as requireing an additional endorsement.
                </p>

                <p>
                    <strong>Enroute Rating Endorsement</strong>
                </p>

                <p>
                    All UK CTR + APP + ADC positions with the exception of those listed in the ATC Training Policy as requireing an additional endorsement.
                </p>

                <p>
                    <strong>Additional endorsements are available on request subject to capacity.</strong>
                    You must first hold a rating endorsement before you can request any additional endorsements.
                </p>

                <p>
                    <strong>Oceanic Endorsement</strong>
                </p>

                <p>
                    Shanwick Radio + Gander Radio
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-history"></i> &thinsp; Application History
                </div>
                <div class="panel-body">
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
        </div>
    </div>
@stop
