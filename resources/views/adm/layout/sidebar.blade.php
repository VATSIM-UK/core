<section class="sidebar">

    {!! Form::open(["url" => URL::route("adm.search"), "method" => "GET", "class" => "sidebar-form"]) !!}
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search..."/>
        <span class="input-group-btn">
                <button type='submit' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
            </span>
    </div>
    {!! Form::close() !!}

    <ul class="sidebar-menu">

        @include('adm.layout.sidebar.membership')

        @include('adm.layout.sidebar.atc')

        @include('adm.layout.sidebar.smartCARS')

        @include('adm.layout.sidebar.visitingTransferring')

    </ul>

</section>
