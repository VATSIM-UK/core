@extends('visit-transfer.site.application._layout')

@section('vt-content')
    <div class="row" id="termsBoxHelp">
        <div class="col-md-12">
            @include('components.html.panel_open', [
                'title' => 'Terms & Conditions',
                'icon' => ['type' => 'fa', 'key' => 'list'],
                'attr' => []
            ])
            <div class="hidden-xs">
                    <p>
                        Before you can start your application, you must first read and agree to the terms and conditions of the
            @include('components.html.panel_close')
        </div>
    </div>
@endsection
