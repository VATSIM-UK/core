@extends('adm.layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <div class="box-title">Waiting List Management</div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12">Select a Waiting List to Manage</div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th># Of Users</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @each('adm.training._tableRow', $lists, 'list')
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop