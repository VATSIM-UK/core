@extends('adm.layout')

@section('content')
    <section class="content">
        <div class="row">
            <div class="col xs-12">
                <div class="box box-warning">
                    <div class="box-header">
                        <div class="box-title">Visiting Controllers Monitoring</div>
                            @include('visit-transfer.admin.hours._searchForm')
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop

@section('scripts')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js" integrity="sha384-8Lb23xW0dVl+HHrv90JF6PpwygXa7Z0zZIK9+RWorNDyubrG7Ppu7JJw32U8op0i" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#startDate, #endDate').datetimepicker({
                timepicker:false,
                format: 'Y-m-d'
            });
        });
    </script>
@stop