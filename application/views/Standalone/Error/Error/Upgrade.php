<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= Arr::get($_config, "site.name.long", "") ?></title>

        <!-- CSS -->
        <?= HTML::style("http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.css"); ?>
        <?= HTML::style("http://netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css"); ?>
        <?= HTML::style("media/style/Standalone/design.css"); ?>
        <?= HTML::style("http://fonts.googleapis.com/css?family=Yellowtail"); ?>
        <?= HTML::style("http://fonts.googleapis.com/css?family=Josefin+Slab:600"); ?>
        <?= HTML::style("http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css"); ?>
        <?= HTML::style("media/bootstrap/3/css/summernote.css"); ?>
        <?= HTML::style("media/bootstrap/3/css/summernote-bs3.css"); ?>
        <?= HTML::style("media/bootstrap/3/css/bootstrap-switch.min.css"); ?>

        <!-- Javascript -->
        <?= HTML::script("http://code.jquery.com/jquery-1.9.1.min.js"); ?>
        <?= HTML::script("http://code.jquery.com/ui/1.10.1/jquery-ui.js"); ?>
        <?= HTML::script("media/jquery/js/jquery.cookie.js"); ?>
        <?= HTML::script("http://netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"); ?>
        <?= HTML::script("media/bootstrap/3/js/summernote.min.js"); ?>
        <?= HTML::script("media/bootstrap/3/js/bootstrap-switch.min.js"); ?>

        <?php foreach ($scripts as $s): ?>
            <?= HTML::script($s); ?>
        <?php endforeach; ?>
    </head>
    <body>
        <div class="container container-header">
            <div class="row">
                <div class="col-md-4 header-left">
                    <p align="left"><?= HTML::image("media/style/global/images/logo.png") ?></p>
                </div>
                <div class="col-md-8 header-right">
                    <p align="right"><?= HTML::image("media/style/global/images/slogan.png"); ?></p>
                </div>
            </div>
        </div>
        <div class="container" id="mainContainer">
            <div class="container container-content">
                <div class="content">
                    <div class="content-inner">
                        <h1><?= $_title ?></h1>

                        <p>
                            It seems that the website is currently undergoing some maintenance.  A new version of the system has been deployed to the server and the team are currently making the necessary
                            changes to install it correctly.
                        </p>
                        <p>
                            This process is usually very quick and should occur without issue.
                        </p>
                        <p>
                            For more specific information about this upgrade, please visit <a href="http://status.vatsim-uk.co.uk">http://status.vatsim-uk.co.uk</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-footer">
            <div class="footer">
                <div class="row">
                    <p>
                        VATSIM-UK &copy; 2013 - 
                        <a href="http://status.vatsim-uk.co.uk" target="_blank">
                            Version <?=__VERSION__?> (<?=__VERSION_DATE__?>)
                        </a>
                        <br align="center">
                        Got a problem? Email us: <?= Html::anchor('http://helpdesk.vatsim-uk.co.uk/index.php?act=tickets&code=open&step=2&department=2', 'web-support][at][vatsim-uk.co.uk', array('target' => '_blank')) ?>
                    </p>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript" language="javascript">
        $(".tooltip_displays").tooltip();
    </script>
</html>