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

	<div class="staff-management container">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Management
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $managementDirectory,
						])
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="staff-departments container">
		<div class="staff-departments__row row">
			<div class="staff-departments__department col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Operations
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $operationsDirectory,
						])
					</div>
				</div>
			</div>
			<div class="staff-departments__department col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; ATC Training
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $atcTrainingDirectory,
						])
					</div>
				</div>
			</div>
		</div>

		<div class="staff-departments__row row">
			<div class="staff-departments__department col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Pilot Training
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $pilotTrainingDirectory,
						])
					</div>
				</div>
			</div>
			<div class="staff-departments__department col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Technology
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $technologyDirectory,
						])
					</div>
				</div>
			</div>
		</div>

		<div class="staff-departments__row row">
			<div class="staff-departments__department col-md-6">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Community
					</div>
					<div class="panel-body">
						@include('site.partials.staff-department', [
							'directory' => $communityDirectory,
						])
					</div>
				</div>
			</div>
		</div>
	</div>
@stop
