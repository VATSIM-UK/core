@extends('adm.layout')

@section('content')
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-inbox"></i> Latest Feedback Submissions</h3>
                <div class="btn-group pull-right">
                    <div style="display:inline-block">
                        {{ $feedback->links() }}
                    </div>
                    @if(isset($form))
                    <div style="display:inline-block">
                        <a href="{{route('adm.mship.feedback.form.export', $form)}}" class="btn btn-primary"> <i class="fa fa-share"></i> Export</a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="box-body">
              Page <b>{{ $feedback->currentPage() }}</b> of <b>{{ $feedback->lastPage() }}</b> | <b>{{ $feedback->count() }}</b> submissions of <b>{{ $feedback->total() }}</b> total
              <table id="mship-accounts" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                          <th class="col-md-1">
                              ID
                          </th>
                          <th class="col-md-3">
                                Subject of Feedback
                          </th>
                          <th>Feedback Form</th>
                          <th>Date Submitted</th>
                          <th>Action Taken</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($feedback as $f)
                      <tr>
                          <td>{!! link_to_route('adm.mship.feedback.view', $f->id, [$f->id]) !!}</td>
                          <td>
                            @if ($f->form->targeted)
                              {{ $f->account->real_name }}
                            @else
                              N/A
                            @endif
                          </td>
                          <td>{{ $f->form->name }}</td>
                          <td>{{ $f->created_at->format("d-m-Y H:i A") }}</td>
                          <td>
                            @if ($f->actioned_at)
                                {!! HTML::img("tick_mark_circle", "png", 35, 47) !!}
                            @else
                                {!! HTML::img("cross_mark_circle", "png", 35, 47) !!}
                            @endif
                          </td>
                      </tr>
                      @endforeach
                  </tbody>
              </table>
              <div class="col-md-4 col-md-offset-4 text-center">
                {{ $feedback->links() }}
              </div>
            </div>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop
