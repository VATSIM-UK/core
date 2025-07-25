@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row" id="statementHelp">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'Choose your Facility',
                'icon' => ['type' => 'fa', 'key' => 'question']
            ])
            <form action="{{ route('visiting.application.statement.post', $application->public_id) }}" method="POST">
                @csrf
            <div class="row">
                <div class="col-md-6 col-md-offset-3">

                    <p>
                        Please justify your application to the facility (<strong>{{ $application->facility->name }}</strong>) in the space provided below.
                        Remember to explain what your motivation is for applying to {{ $application->is_visit ? "visit" : "transfer to" }} <strong>{{ $application->facility->name }}</strong>.
                    </p>
                </div>

                <div class="clear-both"></div>

                <div class="col-md-10 col-md-offset-1">
                    <textarea name="statement" class="form-control">{{ old('statement', $application->statement) }}</textarea>
                </div>

                <div class="clear-both"></div>

                <div class="col-md-12 text-center">
                    <br />
                    <button type="submit" class="btn btn-success">SUBMIT SUPPORTING STATEMENT</button>
                </div>

            </div>
            </form>
            @include('components.html.panel_close')
        </div>
    </div>
@stop
