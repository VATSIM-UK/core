<div class="container container-navbar">
    <div class="navbar">
        <div class="navbar-inner">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!--<a class="brand" href="<?= URL::site() ?>"><?= $config_site_title ?></a>-->
            <div class="nav-collapse collapse">
                <ul class="nav">
                    <li class="active"><a href="<?= URL::site("homepage") ?>">Home</a></li>
                    <li><a href="<?= URL::site("page/about") ?>">About Us</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?= URL::site("account/register") ?>">Sign up!</a></li>
                            <li><a href="<?= URL::site("account/types") ?>">Account Types</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form pull-right navbar-login" action="<?= URL::site("account/login") ?>" method="POST">
                    <input type="email" name="email" placeholder="Email address..." class="col-md-2">
                    <input type="password" name="password" placeholder="Your password..." class="col-md-2">
                    <button type="submit" class="btn btn-inverse">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>