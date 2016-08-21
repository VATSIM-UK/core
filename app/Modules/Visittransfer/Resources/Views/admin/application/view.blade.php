@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-warning">
                <div class="box-header">
                    <div class="box-title">Administrative Actions</div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            @if($unacceptedReferences->count() > 0)
                                {!! Button::danger("Not all references reviewed.")->disable()->withAttributes(["class" => "pull-left"]) !!}

                                {!! Button::success("Not all references reviewed.")->disable()->withAttributes(["class" => "pull-right"]) !!}
                            @else
                                {!! Button::danger("Reject Application")->asLinkTo(route("visiting.admin.application.reject", [$application->id]))->withAttributes(["class" => "pull-left"]) !!}

                                {!! Button::success("Access Application")->asLinkTo(route("visiting.admin.application.accept", [$application->id]))->withAttributes(["class" => "pull-right"]) !!}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Application Content #{{ $application->public_id }}
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">

                            <table class="table table-striped table-bordered table-hover">
                                <tbody>
                                <tr>
                                    <th class="col-md-2">Applicant</th>
                                    <td>
                                        @include("adm.partials._account_link", ["account" => $application->account])
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $application->type_string }}</td>
                                </tr>
                                <tr>
                                    <th>Facility</th>
                                    <th>{{ $application->facility->name }}</th>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <th>
                                        {{ $application->created_at->diffForHumans() }}
                                        //
                                        {{ $application->created_at }}
                                    </th>
                                </tr>
                                <tr>
                                    <th>Submitted At</th>
                                    <th>
                                        {{ $application->submitted_at->diffForHumans() }}
                                        //
                                        {{ $application->submitted_at }}
                                    </th>
                                </tr>
                                <tr>
                                    <th>Statement</th>
                                    <th>
                                        {!! ($application->statement ? nl2br($application->statement) : "None Supplied") !!}
                                    </th>
                                </tr>
                                </tbody>
                            </table>

                        </div>

                        <div class="col-md-6">
                            @forelse($application->referees as $reference)
                                <table class="table table-hover table-bordered table-condensed alert-warning">
                                    <tr>
                                        <th class="col-md-2">Referee</small></th>
                                        <td>
                                            @include("adm.partials._account_link", ["account" => $reference->account])
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Referee Rating</th>
                                        <td>
                                            @include("mship.partials._qualification", ["qualification" => $reference->account->qualification_atc])
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Reference</th>
                                        <td>{!! nl2br($reference->reference) !!}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            {!! Button::danger("Reject Reference")->asLinkTo(route("visiting.admin.reference.reject", [$reference->id]))->withAttributes(["class" => "pull-left"]) !!}

                                            {!! Button::success("Accept Reference")->asLinkTo(route("visiting.admin.reference.accept", [$reference->id]))->withAttributes(["class" => "pull-right"]) !!}
                                        </td>
                                    </tr>
                                </table>
                            @empty
                                <p class="text-center">There are no references associated with this application.</p>
                            @endforelse
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop