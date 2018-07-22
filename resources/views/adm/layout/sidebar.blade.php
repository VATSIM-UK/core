<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
        <div class="pull-left image">
            {!! HTML::image("images/default_avatar.png", "User Image", ["class" => "img-circle", "style" => "background: #FFFFFF;"]) !!}
        </div>
        <div class="pull-left info">
            <p>Hello, {{ $_account->name_first }}</p>

            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
    </div>

    {!! Form::open(["url" => URL::route("adm.search"), "method" => "GET", "class" => "sidebar-form"]) !!}
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search..."/>
            <span class="input-group-btn">
                <button type='submit' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
            </span>
        </div>
    {!! Form::close() !!}

    <ul class="sidebar-menu">

        @include('adm.layout.sidebar.dashboard')

        @include('adm.layout.sidebar.membership')

        @include('adm.layout.sidebar.feedback')

        @include('adm.layout.sidebar.operations')

        @include('adm.layout.sidebar.atc')

        @include('adm.layout.sidebar.smartCARS')

        @include('adm.layout.sidebar.networkData')

        @include('adm.layout.sidebar.visitingTransferring')

        @include('adm.layout.sidebar.system')

    </ul>
</section>
<!-- /.sidebar -->
