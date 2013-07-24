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
                    <p align="left"><img src="<?= URL::site("media/style/global/images/logo.png") ?>" alt="" /></p>
                </div>
                <div class="span8 header-right">
                    <p align="right"><img src="<?= URL::site("media/style/global/images/slogan.png"); ?>" alt="" /></p>
                </div>
            </div>
        </div>
        <div class="container container-content">
            <div class="content">
                <h1>EMail Confirmation</h1>
                <p>
                    <?=$_account->name_first." ".$_account->name_last?>,
                </p>
                <p>
                    Sorry to interrupt you - but it seems we do not have a valid email address stored in our database for you.  This could be for one a few reasons such as
                    opening a new account with us, a syncronisation error with the main VATSIM database or because you haven't logged in within the last 3 months.
                </p>
                <p>
                    To continue utilising our systems, please provide your current VATSIM email address so that we can store this for future reference.
                </p>
                <p>
                    Should you have any questions, comments or concerns, please contact <?= Html::anchor('http://helpdesk.vatsim-uk.co.uk/index.php?act=tickets&code=open&step=2&department=2', 'web-services][at][vatsim-uk.co.uk', array('target' => '_blank')) ?>.
                </p>
                <p>
                    Thanks,
                </p>
                <p class="signature-fancy">
                    The VATSIM-UK Web Services Team
                </p>
            </div>
            <div class="content">
                <h1>Confirm Email</h1>
                <p>
                    To confirm your main VATSIM email address, please enter it below.
                </p>

                <?php if(count($_errors) > 0): ?>
                    <div class="alert alert-error">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Warning! You have the following errors:</strong>
                        <ul>
                            <?php foreach($_errors as $e): ?>
                                <li><?=$e?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row-fluid">
                    <div class="span6 offset2">
                        <form class="form-horizontal form-login" method="POST" action="<?= URL::site("account/manage/email_confirm") ?>">
                            <div class="control-group">
                                <label class="control-label" for="cid">EMail</label>
                                <div class="controls">
                                    <input type="text" id="email" name="email" placeholder="E-Mail">
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="controls">
                                    <button type="submit" class="btn">Confirm EMail</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>