@extends('visit-transfer.site.reference._layout')

@section('vt-content')
    <div class="row">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'Reference Relationship',
                'icon' => ['type' => 'fa', 'key' => 'question-circle']
            ])
            <div class="text-center">
              <p class="text-center">
                <b>Do you know {{ $application->account->name }}?</b></br>
                If you don't know the applicant, please press the button below. This will cancel your reference, and the application will be reviewed by Community staff.</br>
                <form action="{{ route('visiting.reference.complete.cancel', $token->code) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">I do not know the applicant</button>
                </form>
              </p>
            </div>

            @include('components.html.panel_close')
            @include('components.html.panel_open', [
                'title' => 'Reference Content',
                'icon' => ['type' => 'fa', 'key' => 'list']
            ])
            <div id="vt_referee_reference_content" class="row">
                <div class="col-md-10 col-md-offset-1">
                    <p>
                        You are completing a reference for {{ $application->account->name }}'s {{ $application->type_string }} application for {{ $application->facility->name }}.
                        This application is bound by the Visiting &amp; Transferring Controller Policy (VTCP).
                        <br />
                        <a href="https://www.vatsim.net/documents/transfer-and-visiting-controller-policy" target="_blank">The VTCP can be located on the VATSIM.net website</a>
                    </p>

                    <p>
                        I can confirm the following:
                    </p>

                </div>

                <form action="{{ route('visiting.reference.complete.post', $token->code) }}" method="POST">
                    @csrf
                <div class="container-fluid">
                  <div class="col-xs-11 col-xs-offset-1 col-md-10 col-md-offset-2">
                    <div class="row">
                        <div class="col-xs-9 col-lg-8">
                            <label for="reference_relationship">I am {{$application->account->name}}'s '{{$reference->relationship}}'&nbsp;&nbsp;</label>
                        </div>
                        <div class="col-xs-3">
                          <label class="btn btn-xs btn-danger checkbox-button {{old('reference_relationship') ? 'active' : ''}}" data-toggle="buttons">
                            <input type="checkbox" name="reference_relationship" value="1" {{ old('reference_relationship') ? 'checked' : '' }}>
                            <span class="fa fa-check"></span>
                          </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-9 col-lg-8">
                            <label for="reference_hours_minimum">{{$application->account->name}} has consolidated their current controller rating as per the V&amp;T policy&nbsp;&nbsp;</label><br />
                            <small class="form-text text-muted">A rating that has not been consolidated cannot be considered for a visit or transfer.</small>
                        </div>
                        <div class="col-xs-3">
                          <label class="btn btn-xs btn-danger checkbox-button {{old('reference_hours_minimum') ? 'active' : ''}}" data-toggle="buttons">
                            <input type="checkbox" name="reference_hours_minimum" value="1" {{ old('reference_hours_minimum') ? 'checked' : '' }}>
                            <span class="fa fa-check"></span>
                          </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-9 col-lg-8">
                            <label for="reference_recent_transfer">{{$application->account->name}} last transferred region, division or VACC in excess of 90 days prior to {{$application->created_at->toDateString()}}&nbsp;&nbsp;</label><br />
                            <small class="form-text text-muted">Applicants may only transfer regions, divisions or VACCs once every 90 days.</small>
                        </div>
                        <div class="col-xs-3">
                          <label class="btn btn-xs btn-danger checkbox-button {{old('reference_recent_transfer') ? 'active' : ''}}" data-toggle="buttons">
                            <input type="checkbox" name="reference_recent_transfer" value="1" {{ old('reference_recent_transfer') ? 'checked' : '' }}>
                            <span class="fa fa-check"></span>
                          </label>
                        </div>
                    </div>
                    @if($application->type == \App\Models\VisitTransfer\Application::TYPE_TRANSFER)
                        <div class="row">
                            <div class="col-xs-9 col-lg-8">
                                <label for="reference_not_staff">{{$application->account->name}} will not hold a staff position in their home division if their application is successful&nbsp;&nbsp;</label><br />
                                <small class="form-text text-muted">Members may only hold a staff position in their home division.</small>
                            </div>
                            <div class="col-xs-3">
                              <label class="btn btn-xs btn-danger checkbox-button {{old('reference_not_staff') ? 'active' : ''}}" data-toggle="buttons">
                                <input type="checkbox" name="reference_not_staff" value="1" {{ old('reference_not_staff') ? 'checked' : '' }}>
                                <span class="fa fa-check"></span>
                              </label>
                            </div>
                        </div>
                    @endif
                  </div>
                </div>

                <div class="col-md-10 col-md-offset-1">

                    <p>
                        <br />
                        Please provide a written reference for {{ $application->account->name }}, detailing why we should accept their request to {{ $application->type_string }} VATSIM UK.
                        <br />
                        <strong>The candidate will not be given automatic access to this content.</strong>
                    </p>

                </div>

                <div class="clear-both"></div>

                <div class="col-md-10 col-md-offset-1">
                    <textarea name="reference" class="form-control"></textarea>
                </div>

                <div class="clear-both"></div>

                <div class="col-md-12 text-center">
                    <br />
                    <button type="submit" class="btn btn-success">SUBMIT REFERENCE</button>
                </div>

                <input type="hidden" name="application_type" value="{{ $application->type }}">

                </form>

            </div>
            @include('components.html.panel_close')
        </div>
    </div>
@stop
