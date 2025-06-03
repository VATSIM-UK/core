@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row" id="submissionHelp">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'Withdraw',
                'icon' => ['type' => 'fa', 'key' => 'tick']
            ])
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <p>
                        You may withdraw your application with no penalty.  To do so, please click the button below.<br>
                        You <strong>will</strong> be able to open another application following this.
                </div>

                <form action="{{ route('visiting.application.withdraw.post', $application->public_id) }}" method="POST">
                    @csrf
                    <div class="col-md-6 col-md-offset-3 text-center">
                        <button type="submit" class="btn btn-danger">WITHDRAW APPLICATION</button>
                    </div>
                </form>

            </div>
            @include('components.html.panel_close')
        </div>
    </div>
@stop
