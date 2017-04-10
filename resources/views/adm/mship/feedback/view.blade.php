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
                  {{ $feedback->account->real_name }} ({{ $feedback->account->id }})
                </div>
                @if (\Auth::user()->hasChildPermission('adm/mship/feedback/view/*/reporter'))
                  <div class="col-md-12">
                    <b>Submitter:</b></br>
                    <span id="hidden-submitter-preamble">
                      <small>View who submitted this feedback. This should <b>only</b> be used when the system has been abused to preserve the submitter's anonymity.</small></br>
                      {{ Form::button('Reveal', ['class' => 'btn btn-danger', 'onclick' => '$("#hidden-submitter").show();$("#hidden-submitter-preamble").hide();']) }}
                    </span>
                    <div id="hidden-submitter" style="display:none">
                      {{ $feedback->submitter->real_name }} ({{ $feedback->submitter->id }})
                    </div>
                  </div>
                @endif
            </div>
          </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="box box-primary">
          <div class="box-header">
              <h3 class="box-title ">
                  Action
              </h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <div class="row">
              <div class="col-md-3">
                <b> Status:</b></br>
                @if ($feedback->actioned_at)
                  {!! HTML::img("tick_mark_circle", "png", 24, 32) !!}
                @else
                  {!! HTML::img("cross_mark_circle", "png", 24, 32) !!}
                @endif
              </div>
                @if ($feedback->actioned_at)
                    <div class="col-md-3">
                      <b>Marked actioned at:</b></br>
                      {{ $feedback->actioned_at->format("d-m-Y H:i A") }}
                    </div>
                    <div class="col-md-3">
                      <b>Marked actioned by:</b></br>
                      {{ $feedback->actioner->real_name }}
                    </div>
                    @if (\Auth::user()->hasChildPermission('adm/mship/feedback/view/*/unaction'))
                      <div class="col-md-3">
                        <a href="{{route('adm.mship.feedback.unaction', [$feedback->id])}}">{{ Form::button('Unmark as Actioned', ['class' => 'btn btn-danger']) }}</a>
                      </div>
                    @endif

                    <div class="col-md-12">
                      <b>Actioned Comment:</b></br>
                      {{ $feedback->actioned_comment }}
                    </div>
                @else
                    @if (\Auth::user()->hasChildPermission('adm/mship/feedback/view/*/action'))
                      {{ Form::open(['route' => ['adm.mship.feedback.action', $feedback->id]]) }}
                      <div class="col-md-9">
                        {{ Form::label('Action Comment')}}
                        {{ Form::textarea('comment', null, ['rows' => 1, "cols"=> 10]) }}
                        {{ Form::submit('Mark Actioned', ['class' => 'btn btn-warning']) }}
                      </div>
                    @endif
                @endif
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
