@extends('layout')

@section('content')

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> &thinsp; Demo Template</div>
                <div class="panel-body">
                    <form action="{{ route('events.store') }}" method="post">
                        {{ csrf_field() }}
                        Task name:
                        <br />
                        <input type="text" name="name" />
                        <br /><br />
                        Task description:
                        <br />
                        <textarea name="description"></textarea>
                        <br /><br />
                        Start time:
                        <br />
                        <input type="text" name="events_date" class="date" />
                        <br /><br />
                        <input type="submit" value="Save" />
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>
    <script>
        $('.date').datepicker({
            autoclose: true,
            dateFormat: "yy-mm-dd"
        });
    </script>
@stop