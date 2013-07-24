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
                <h1>System Error :: Forbidden</h1>
                <p>
                    It appears you've managed to find a protected area.  If you are unsure why you are seeing this, then the chances are you have followed an invalid link.
                </p>
                <p>
                    If you believe you are seeing this message in error, please contact <?= Html::anchor('http://helpdesk.vatsim-uk.co.uk/index.php?act=tickets&code=open&step=2&department=2', 'web-services][at][vatsim-uk.co.uk', array('target' => '_blank')) ?>.
                </p>
                <p>
                    Thanks,
                </p>
                <p class="signature-fancy">
                    The VATSIM-UK Web Services Team
                </p>
            </div>
        </div>
    </body>
</html>