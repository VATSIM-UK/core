<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title>VATSIM UK | Administrative Log in</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        {!! HTML::style('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css') !!}
        <!-- font Awesome -->
        {!! HTML::style('//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css') !!}
        <!-- Theme style -->
        {!! HTML::style('/assets/css/AdminLTE.css') !!}

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="bg-black">

        <div class="form-box" id="login-box">
            <div class="header">Sign In</div>
            <form action="{{ URL::route("adm.authentication.login.post") }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="body bg-gray">
                    <p>This website uses VATSIM's latest SSO system to authenticate you.</p>
                    <p>
                        If you do not have an administrative account <strong>at VATSIM UK</strong>, please *DO NOT* attempt to login as it <strong>will</strong> fail.
                        This is <strong>not</strong> the general membership area.
                    </p>
                    <p>All access attempts are logged and monitored.</p>
                </div>
                <div class="footer">
                    <button type="submit" class="btn bg-olive btn-block">Sign In Using SSO</button>
                </div>
            </form>
        </div>


        <!-- jQuery 2.0.2 -->
        {!! HTML::script('https://code.jquery.com/jquery-2.1.1.min.js') !!}
        <!-- Bootstrap -->
        {!! HTML::script('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js') !!}

    </body>
</html>