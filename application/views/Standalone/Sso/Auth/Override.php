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
                <h1>Account Override</h1>
                <p>
                    Please enter the CID of an account to override, along with your second security layer details.
                </p>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-error">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Warning! An error occured:</strong>
                        <ul>
                            <li><?=$error?></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row-fluid">
                    <div class="span6 offset2">
                        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("sso/auth/override") ?>">
                            <div class="control-group">
                                <label class="control-label" for="override_cid">Account CID</label>
                                <div class="controls">
                                    <input type="text" id="override_cid" name="override_cid" placeholder="Account CID">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label" for="extra_password">Extra Password</label>
                                <div class="controls">
                                    <input type="password" id="extra_password" name="extra_password" placeholder="Extra Password">
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <button type="submit" class="btn" name="processoverride" value="override">Override Account</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-footer">
            <div class="footer">
                <div class="row-fluid">
                    <p>
                        VATSIM-UK &copy; 2013 - Version <?=exec("git describe --abbrev=0 --tags")?>
                        &bull;
                        Got a problem? Email us: <?= Html::anchor('http://helpdesk.vatsim-uk.co.uk/index.php?act=tickets&code=open&step=2&department=2', 'web-support][at][vatsim-uk.co.uk', array('target' => '_blank')) ?>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>