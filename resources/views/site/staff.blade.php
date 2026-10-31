@extends('layout')

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Staff
					</div>
					<div class="panel-body">
						<p>VATSIM UK is led by the Division Director and is managed at a strategic level by the Division
							Staff Group comprised of the heads of each department. Department staff may be appointed
							that
							report to the relevant member of the DSG.</p>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Contact Us
					</div>
					<div class="panel-body text-center">
						<p>Need to talk to a member of staff? All our staff members can be contacted through our
							<strong>helpdesk</strong>
						</p>
						<a href="https://helpdesk.vatsim.uk" target="_blank" class="btn btn-info">Contact Staff / Department
							<i class="fa fa-chevron-right"></i></a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Management
					</div>
					<div class="panel-body">
						<div class="col-md-6">
							<h4 class="text-center">Division Director (VATUK1)</h4><br />
							<p class="text-center">Ben Wright</p>
						</div>
						<div class="col-md-6">
							<h4 class="text-center">Deputy Division Director (VATUK2)</h4><br />
							<p class="text-center">Nick Marinov</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<!-- Row One -->
		<!-- Row Two -->
		<div class="row">
			<!-- Operations -->
			<div class="col-md-4">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Operations
					</div>
					<div class="panel-body">
						<h4 class="text-center">Operations Director (VATUK9)</h4><br />
						<p class="text-center"><em>Vacant</em></p>
						<h4 class="text-center">Operations Team</h4>
						<table class="table">
							<tr>
								<td>Deputy Operations Director</td>
								<td>Ben Walker</td>
							</tr>
							<tr>
								<td>Enroute Operations Coordinator</td>
								<td><em>Vacant</em></td>
							</tr>
							<tr>
								<td>Aerodrome Operations Coordinator</td>
								<td><em>Vacant</em></td>
							</tr>
							<tr>
								<td>Sector File Coordinator</td>
								<td>Luke Brown</td>
							</tr>
							<tr>
								<td>Sector File Coordinator</td>
								<td>Lily Unitt</td>
							</tr>
							<tr>
								<td>Events Manager</td>
								<td><em>Vacant</em></td>
							</tr>
							<tr>
								<td>Events Assistant</td>
								<td>Alex Heirbaut Kitteridge</td>
							</tr>
							<tr>
								<td>Operations Assistant</td>
								<td>Alice Ford</td>
							</tr>
							<tr>
								<td>Operations Assistant</td>
								<td>Chad Byworth</td>
							</tr>
							<tr>
								<td>Operations Assistant</td>
								<td>1237658</td>
							</tr>
							<tr>
								<td>Operations Assistant</td>
								<td>Jack Edwards</td>
							</tr>
							<tr>
								<td>Operations Assistant</td>
								<td><em>Vacant</em></td>
							</tr>
							<tr>
								<td>Operations Assistant</td>
								<td>Oliver Cesarczyk</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<!-- End Operations -->
			<!-- Training -->
			<div class="col-md-8">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Training
					</div>
					<div class="panel-body">
						<div class="col-md-6">
							@include('site.partials.staff-department', [
								'directory' => $atcTrainingDirectory,
								'teamHeading' => 'ATC Training Team',
							])
						</div>

						<div class="col-md-6">
							<h4 class="text-center">Pilot Training Director (VATUK6)</h4><br />
							<p class="text-center">Ben Carpenter</p>
							<h4 class="text-center">Pilot Training Team</h4>
							<table class="table">
								<tr>
									<td>Pilot Training Manager</td>
									<td>Lewis Hammett</td>
								</tr>
								<tr>
									<td>Initial Flight Instructor</td>
									<td>Andy White</td>
								</tr>
								<tr>
									<td>VFR Flight Instructor</td>
									<td>Aiden Bok</td>
								</tr>
								<tr>
									<td>VFR Flight Instructor</td>
									<td><em>Vacant</em></td>
								</tr>
								<tr>
									<td>IFR Flight Instructor</td>
									<td>Firas Bashee</td>
								</tr>
								<tr>
									<td>Advanced Flight Instructor</td>
									<td>Charlie Innam</td>
								</tr>
								<tr>
									<td>Advanced Flight Instructor</td>
									<td><em>Vacant</em></td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- End Training -->
		</div>
	</div>
	<div class="container">
		<div class="row row-eq-height">
			<!-- Web Services -->
			<div class="col-md-4">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Technology
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $technologyDirectory,
							'teamHeading' => 'Technology Team',
						])
					</div>
				</div>
			</div>
			<!-- End Web Services -->
			<!-- Community -->
			<div class="col-md-4">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Community
					</div>
					<div class="panel-body">
						<h4 class="text-center">Community Director (VATUK3)</h4><br />
						<p class="text-center">William Shaw</p>
						<h4 class="text-center">Community Team</h4>
						<table class="table">
							<tr>
								<td>Outreach Manager</td>
								<td><em>Vacant</em></td>
							</tr>
							<tr>
								<td>Outreach Assistant</td>
								<td>Callum Hicks</td>
							<tr>
								<td>Outreach Assistant</td>
								<td>Charles Newman</td>
							</tr>
							<tr>
								<td>Outreach Assistant</td>
								<td>Noah Murdoch</td>
							</tr>
							<tr>
								<td>VT Assistant</td>
								<td>Dylan Parkes</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<!-- End Community -->
		</div>
		<!-- End Row One -->

	</div>
	<!-- End Row Two -->
	</div>
@stop
