@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row" id="termsBoxHelp">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'Terms & Conditions',
                'icon' => ['type' => 'fa', 'key' => 'list']
            ])
            <div class="hidden-xs">
                    <p>
                        Before you can start your application, you must first read and agree to the terms and conditions of the
                        Visiting &amp; Transferring Controller Policy (VTCP).
                        <a href="https://www.vatsim.net/docs/policy/transfer-and-visiting-controller-policy" target="_blank">The VTCP can be located on the VATSIM.net website.</a>
                    </p>

                <form action="{{ route('visiting.application.start.post', [$applicationType, $trainingTeam]) }}" method="POST">
                    @csrf
                    <div class="col-md-10 col-md-offset-1">
                        <div class="form-group">
                            <input id="terms_read" name="terms_read" type="checkbox" value="1">
                            <label for="terms_read" class="control-label"> I confirm that I have read the Visiting &amp; Transferring Controller Policy</label>
                            <span class="help-block">Your application will be rejected if you do not meet the requirements of this policy.</span>
                        </div>

                        <div class="form-group">
                            <input id="terms_one_hour" name="terms_one_hour" type="checkbox" value="1">
                            <label for="terms_one_hour" class="control-label"> I confirm that I will complete this application within 60 minutes</label>
                            <span class="help-block">After 60 minutes your application will automatically be deleted if it isn't submitted.</span>
                        </div>

                        <div class="form-group">
                            <input id="terms_hours_minimum" name="terms_hours_minimum" type="checkbox" value="1">
                            <label for="terms_hours_minimum" class="control-label"> I confirm that I have consolidated my rating by controlling for 50 hours in my home division</label>
                            <span class="help-block">A rating that has not been consolidated cannot be considered for an application to visit or transfer.</span>
                        </div>

                        <div class="form-group">
                            <input id="terms_hours_minimum_relevant" name="terms_hours_minimum_relevant" type="checkbox" value="1">
                            <label for="terms_hours_minimum_relevant" class="control-label"> When consolidating my current rating, the hours were spent on a suitable position for my rating</label>
                            <span class="help-block">For example, if you are a C1 your 50 hours must have been spent on CTR or FSS type positions.</span>
                        </div>

                        <div class="form-group">
                            <input id="terms_recent_transfer" name="terms_recent_transfer" type="checkbox" value="1">
                            <label for="terms_recent_transfer" class="control-label"> I last transferred region, division or VACC at least 90 days prior to the start of my application</label>
                            <span class="help-block">Applicants may only transfer regions, divisions or VACCs once every 90 days.</span>
                        </div>

                        <div class="form-group">
                            <input id="terms_90_day" name="terms_90_day" type="checkbox" value="1">
                            <label for="terms_90_day" class="control-label"> I will complete my local induction plan (if required), or make every attempt to attain full competency within 90 days</label>
                            <span class="help-block">Any application not completing this induction will be transferred back to their previous region/division.</span>
                        </div>

                        @if($applicationType == \App\Models\VisitTransfer\Application::TYPE_TRANSFER)
                            <div class="form-group">
                                <input id="terms_not_staff" name="terms_not_staff" type="checkbox" value="1">
                                <label for="terms_not_staff" class="control-label"> I confirm that I will not hold a staff position in another region, division or VACC if my application is successful</label>
                                <span class="help-block">You may only hold a staff position in your home division.</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 col-md-offset-3 text-center">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>

                    <input type="hidden" name="application_type" value="{{ $applicationType }}">
                    <input type="hidden" name="training_team" value="{{ $trainingTeam }}">

                </form>

            </div>
            @include('components.html.panel_close')
        </div>
    </div>
@stop
