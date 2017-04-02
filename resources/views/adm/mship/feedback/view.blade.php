@extends('adm.layout')

@section('content')

<div class="row">
    <div class="col-md-6">
      <div class="box box-primary">
          <div class="box-header">
              <h3 class="box-title ">
                  Feedback Details
              </h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                  <b> Submitted at:</b></br>
                  {{ $feedback->created_at->format("d-m-Y H:i A") }}
                </div>
                <div class="col-md-3">
                  <b> Target Department:</b></br>
                  {{ $feedback->isATC() ? "ATC" : "Pilot"  }}
                </div>
                <div class="col-md-3">
                  <b> Target Memeber:</b></br>
                  {{ $feedback->account->name_first }} {{ $feedback->account->name_last }} ({{ $feedback->account->id }})
                </div>
            </div>
          </div>
      </div>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title ">
                    Questions &amp; Answers
                </h3>
            </div><!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                @foreach ($feedback->answers()->notPermanent()->get() as $answer)
                  <div class="col-md-4">
                    <div class="box box-warning">
                        <div class="box-header">
                            <h4 class="box-title ">
                                {{ $answer->question()->withTrashed()->first()->question }}
                            </h4>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            {{ $answer->response }}
                        </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
        </div>
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop
