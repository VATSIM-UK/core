@extends('adm.layout')

@section('content')
<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-inbox"></i> Forms</b></h3>
                @if ($_account->hasPermissionTo("adm/mship/feedback/new"))
                  <a href="{{ URL::route("adm.mship.feedback.new") }}" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Create form</a>
                @endif
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-md-12">
                  <table class="table text-center">
                    <tr>
                      <th>Form Name</th>
                      <th>Submissions</th>
                      <th>Number un-actioned</th>
                      <th></th>
                    </tr>
                    @foreach ($forms as $form)
                      <tr>
                        <td>{{$form->name}}</td>
                        <td>{{$form->feedback()->count()}}</td>
                        <td>{{$form->feedback()->unActioned()->count()}}</td>
                        <td>
                          <div class="btn-group" role="group">
                            @if ($_account->hasPermissionTo("adm/mship/feedback/configure/*") || $_account->hasPermissionTo("adm/mship/feedback/configure/".$form->slug))
                              <a type="button" href="{{ URL::route("adm.mship.feedback.config", [$form->slug]) }}" class="btn btn-warning"><i class="ion ion-gear-a"></i> Edit form</a>
                            @endif
                            {{-- @if ($_account->hasPermissionTo("adm/mship/feedback/list/*") || $_account->hasPermissionTo("adm/mship/feedback/list/".$form->slug))
                              <a type="button" href="{{ URL::route("adm.mship.feedback.form", [$form->slug]) }}" class="btn btn-success"><i class="ion ion-search"></i> View Submissions</a>
                            @endif --}}
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </table>
                </div>
            </div>
        </div><!-- /.nav-tabs-custom -->
    </section><!-- /.Left col -->
</div><!-- /.row (main row) -->
@stop
