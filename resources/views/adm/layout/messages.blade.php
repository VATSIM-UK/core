@if(Session::get("success", false))
    <div class='col-md-12'>
        <div class="alert alert-success">
            <i class="fa fa-check"></i>
            <b>Success!</b><br />
            {{ Session::pull('success') }}
        </div>
    </div>
@endif

@if(Session::get("error", false))
    <div class='col-md-12'>
        <div class="alert alert-danger">
            <i class="fa fa-ban"></i>
            <b>Error!</b><br />
            {{ Session::pull('error') }}
        </div>
    </div>
@endif
