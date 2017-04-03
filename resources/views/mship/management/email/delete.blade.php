@extends('layout')

@section('content')
  <div class="panel panel-ukblue">
    <div class="panel-heading"> Delete Secondary Email</div>
    <div class="panel-body text-center">
      <!-- Content Of Panel [START] -->
      <p>
        Are you sure you would like to delete <b>{{ $email->email }}</b> from your secondary emails?
      </p>

      @if ($assignments->count() > 0)
        <p>
          Deleting this email will reset the following SSO assignments to their default value ({{ \Auth::user()->email }})
            <div align="center">
                @foreach ($assignments as $assignment)
                  &bull; {{ $assignment->ssoAccount->name }}</br>
                @endforeach
            </div>
        </p>
      @endif
      {!! Form::open(["route" => ["mship.manage.email.delete.post", $email->id], "class" => "form-horizontal"]) !!}
      <div class="form-group">
        <button type="submit" class="btn btn-danger" name=""
        value="delete_email">Delete</button>
      </div>
      {!! Form::close() !!}
      <!-- Content Of Panel [END] -->
    </div>
  </div>

@stop
