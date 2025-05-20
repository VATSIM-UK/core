@extends('adm.layout')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title ">
                        Create New Facility
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    @if(isset($facility) && $facility->exists)
                        <form method="POST" action="{{ route('adm.visiting.facility.update.post', $facility->id) }}">
                            @csrf
                    @else
                        <form method="POST" action="{{ route('adm.visiting.facility.create.post') }}">
                            @csrf
                    @endif

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $facility->name) }}">
                            </div>
                            <div class="form-group
