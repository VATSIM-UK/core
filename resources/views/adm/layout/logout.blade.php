<div class="navbar-custom-menu">
    <ul class="nav navbar-nav">
        <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="glyphicon glyphicon-user"></i>
                <span>{{ $_account->name_first . ' ' . $_account->name_last }}<i class="caret"></i></span>
            </a>
            <ul class="dropdown-menu">
                <li class="user-header bg-light-blue">
                    {!! HTML::image("images/default_avatar.png", "User Image", ["class" => "img-circle"]) !!}
                    <p>
                        {{ $_account->name_first . ' ' . $_account->name_last }}
                    </p>
                </li>
                <li class="user-footer">
                    <div class="pull-left">
                        <a href="{{ route('dashboard') }}" class="btn btn-default btn-flat">DASHBOARD</a>
                    </div>
                    <div class="pull-right">
                        {!! Form::open(['route' => 'logout', 'id' => 'logout-form']) !!}
                        <a href="{{ route('logout') }}" class="btn btn-default btn-flat" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">LOG OUT</a>
                        {!! Form::close() !!}
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</div>
