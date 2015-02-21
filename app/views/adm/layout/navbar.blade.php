<ul class="nav navbar-nav">
    <!-- User Account: style can be found in dropdown.less -->
    <li class="dropdown user user-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="glyphicon glyphicon-user"></i>
            <span>{{ Auth::admin()->get()->name_first . ' ' . Auth::admin()->get()->name_last }}<i class="caret"></i></span>
        </a>
        <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header bg-light-blue">
                {{ HTML::image("assets/images/default_avatar.png", "User Image", ["class" => "img-circle"]) }}
                <p>
                    {{ Auth::admin()->get()->name_first . ' ' . Auth::admin()->get()->name_last }}
                    <small>Member since {{ Auth::admin()->get()->created_at->diffForHumans() }} <br /><em> {{ Auth::admin()->get()->created_at->toDateTimeString() }}</em></small>
                </p>
            </li>
            <!-- Menu Body -->
            <!--<li class="user-body">
                <div class="col-xs-4 text-center">
                    <a href="#">Followers</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a href="#">Sales</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a href="#">Friends</a>
                </div>
            </li>-->
            <!-- Menu Footer-->
            <li class="user-footer">
                <!--<div class="pull-left">
                    <a href="#" class="btn btn-default btn-flat">Profile</a>
                </div>-->
                <div class="pull-right">
                    <a href="{{ URL::route("adm.authentication.logout") }}" class="btn btn-default btn-flat">Sign out</a>
                </div>
            </li>
        </ul>
    </li>
</ul>
