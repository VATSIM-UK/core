<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.2" />
        <title><?= $config_site_title ?></title>

        <!-- CSS -->
        <?= HTML::style("http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.css"); ?>
        <?= HTML::style("media/bootstrap/css/bootstrap.min.css"); ?>
        <?= HTML::style("media/bootstrap/css/bootstrap-responsive.min.css"); ?>
        <?= HTML::style("media/style/Standalone/design.css"); ?>
        <?= HTML::style("http://fonts.googleapis.com/css?family=Yellowtail"); ?>

        <!-- Javascript -->
        <?= HTML::script("http://code.jquery.com/jquery-1.9.1.min.js"); ?>
        <?= HTML::script("http://code.jquery.com/ui/1.10.1/jquery-ui.js"); ?>
        <?= HTML::script("media/bootstrap/js/bootstrap.min.js"); ?>
        <?= HTML::script("media/scripts/general.js"); ?>
    </head>
    <body>
        <div class="container container-header">
            <div class="row-fluid">
                <div class="span4 header-left">
                    <p align="left"><?= HTML::image("media/style/global/images/logo.png") ?></p>
                </div>
                <div class="span8 header-right">
                    <p align="right"><?= HTML::image("media/style/global/images/slogan.png"); ?></p>
                </div>
            </div>
        </div>
        <div class="container container-content">
            <div class="content">
                <h1>New Central Login System</h1>
                <p>
                    Dear members,
                </p>
                <p>
                    In an effort to centralise all of our services, we have now created a central login system.  Over the coming months more and more
                    features will be moved over to this new area, which will replace a large proportion of the administrative interfaces members encounter currently.
                </p>
                <p>
                    Should you have any questions, comments or concerns, please contact <?= Html::anchor('http://helpdesk.vatsim-uk.co.uk/index.php?act=tickets&code=open&step=2&department=2', 'web-services][at][vatsim-uk.co.uk', array('target' => '_blank')) ?>.
                </p>
                <p>
                    Much love,
                </p>
                <p class="signature-fancy">
                    The VATSIM-UK Web Services Team
                </p>
            </div>
            <div class="content">
                <h1>Login</h1>
                <p>
                    To login to the <?= (isset($_GET["area"]) ? $_GET["area"] : "VATUK system") ?>, please enter your VATSIM Certificate ID and network password below.
                </p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Warning! An error occured:</strong>
                        <ul>
                            <li><?= $error ?></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row-fluid">
                    <div class="span6 offset2">
                        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/auth/login") ?>">
                            <div class="control-group">
                                <label class="control-label" for="cid">CID</label>
                                <div class="controls">
                                    <input type="text" id="cid" name="cid" placeholder="CID">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="password">Password</label>
                                <div class="controls">
                                    <input type="password" id="password" name="password" placeholder="Password">
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <button type="submit" class="btn" name="processlogin" value="login">Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>