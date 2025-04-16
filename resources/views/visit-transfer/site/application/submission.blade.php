@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row" id="submissionHelp">
        <div class="col-md-12">
            {!! HTML::panelOpen("Submission", ["type" => "fa", "key" => "tick"]) !!}
            <div class="row">
                <div class="col-md-10 col-md-offset-1">

                    <p>

                    </p>

                </div>

                <form action="{{ route('visiting.application.submit.post', $application->public_id) }}" method="POST">
                    @csrf
                    <div class="col-md-10 col-md-offset-1 text-center">
                        <div class="form-group row">
                            <label for="submission_terms">
                                I confirm that the details within this application are correct to the best of my
                                knowledge and that my application will be rejected if any details are inaccurate. I
                                understand that after I submit this application I will not be able to amend any details
                                (e.g. referee details) and in the event of any details being incorrect, I will need to
                                start a new application.
                            </label>
                            <input type="checkbox" name="submission_terms" id="submission_terms" value="1">
                        </div>
                    </div>

                    <div class="col-md-10 col-md-offset-1 text-center">
                        <button type="submit" class="btn btn-success">SUBMIT APPLICATION</button>
                    </div>
                </form>

            </div>
            {!! HTML::panelClose() !!}
        </div>
    </div>
@stop
