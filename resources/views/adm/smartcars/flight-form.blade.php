@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">smartCARS Airport</h3>
                </div>
                <div class="box-body">
                    {!! Form::open(['method' => 'put', 'route' => ['adm.smartcars.flights.update', $flight]]) !!}

                    <label>Pass/Fail<i class="fa fa-asterisk text-danger"></i></label>
                    <div class="radio" style="margin-top: 0;">
                        <label>
                            <input type="radio" name="passed" value="1" {{ $flight->passed ? 'checked' : '' }}> Passed {{ $flight->passed ? '- Current' : '' }}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="passed" value="0" {{ !$flight->passed ? 'checked' : '' }}> Failed {{ !$flight->passed ? '- Current' : '' }}
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason for Change (Visible to User)<i class="fa fa-asterisk text-danger"></i></label>
                        <input type="text" id="reason" name="reason" class="form-control"
                               value="{{ old('reason') ?: $flight->pass_reason }}" required>
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">
                    <a class="btn btn-default" href="{{ route('adm.smartcars.flights.index') }}">Cancel</a>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
