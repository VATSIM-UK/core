@extends('adm.layout')

@section('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js" integrity="sha384-8Lb23xW0dVl+HHrv90JF6PpwygXa7Z0zZIK9+RWorNDyubrG7Ppu7JJw32U8op0i" crossorigin="anonymous"></script>
  <script type="text/javascript">
  $(document).ready(function () {
      $('#from, #to').datetimepicker({
        timepicker:false,
        format: 'Y/m/d'
      });
  });
  </script>
@endsection

@section('content')
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-inbox"></i> Export <b>{{$form->name}}</b></h3>
            </div>
            <div class="box-body">
              {{Form::open(['route' => ['adm.mship.feedback.form.export.post', $form->slug], 'method' => 'post'])}}
              <div class="row">
                <div class="col-xs-6 col-md-4">
                      {!! Form::label("from","From",['class' => 'control-label']),
                      Form::text("from", '', ['class' => 'form-control']) !!}

                </div>
                <div class="col-xs-6 col-md-4">
                        {!! Form::label("to","To",['class' => 'control-label']),
                        Form::text("to", '', ['class' => 'form-control']) !!}
                </div>
                <div class="col-xs-6 col-md-4">
                  <b>Include:</b>
                  <div class="form-group">
                    <div>Actioned Feedback {{Form::checkbox("include_actioned", true)}}</div>
                    <div>Un-Actioned Feedback {{Form::checkbox("include_unactioned", true)}}</div>
                    <div>CID and Name of Target (if applicable) {{Form::checkbox("include_target", true)}}</div>
                  </div>
                </div>
                <div class="col-xs-12 text-center">
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
              {{Form::close()}}
            </div>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop
