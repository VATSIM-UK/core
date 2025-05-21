@extends('layout')

@section('content')
<div class="panel panel-ukblue">
	<div class="panel-heading"> Email Assignments</div>
	<div class="panel-body">
			<!-- Content Of Panel [START] -->
		<p>
			When you are logged into systems within VATSIM UK, as standard we use your <strong>primary</strong> email address
			when sending you important emails from that service.
		</p>
		<p>
			If you wish for <em>some</em> services to dispatch emails to an alternative address <strong>instead of your primary email</strong>
			you can specify that using the matrix below.
		</p>
		<p>
			<strong>Please note:</strong> you may only have <em>one</em> email address assigned to a specific system.  Once that email address
			expires (or becomes unverified after a period of time) we will default back to your primary email.
		</p>

        <form action="{{ route('mship.manage.email.assignments') }}" method="POST" class="form-horizontal">
            @csrf
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th class="col-md-2">
						Email &#8594;<br />
						System &#8595;
					</th>
					<th>PRIMARY<br />{{ $userPrimaryEmail }}</th>
					@foreach($userSecondaryVerified as $email)
						<th>SECONDARY<br />{{ $email->email }}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach($userMatrix as $um)
					<tr>
						<th>{{ $um['sso_system']->name }}</th>
						<td>
							@if($um['assigned_email_id'] == $userPrimaryEmail)
								<em>Default</em>
							@else
								<div class="radio">
									<label>
										<input type="radio" name="assign_{{ $um['sso_system']->id }}" value="pri">
										Return to default
									</label>
								</div>
							@endif
						</td>
						@foreach($userSecondaryVerified as $email)
							<td>
								<div class="radio">
									<label>
										<input type="radio" name="assign_{{ $um['sso_system']->id }}" value="{{ $email->id }}" {{ $email->id == $um['assigned_email_id'] ? "checked='checked'" : "" }}>
										Assign
									</label>
								</div>
							</td>
						@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>

		<div class="form-group">
			<div class="col-sm-offset-5 col-sm-7">
				<button type="submit" class="btn btn-default" name="processassignments"
						value="new_email_add">Save Assignments
				</button>
			</div>
		</div>

        </form>
		<!-- Content Of Panel [END] -->
	</div>
</div>
@stop
