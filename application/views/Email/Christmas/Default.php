<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div class="container container-header">
            <div class="col-md-4 header-left">
                <p align="left" style="width: 40%; float: left;"><?= HTML::image(URL::site("", "http") . "media/style/global/images/logo.png") ?></p>
                <p align="right" style="padding-top: 20px; width: 60%; float: right;"><?= HTML::image(URL::site("", "http") . "media/style/global/images/slogan.png"); ?></p>
            </div>
            <p style="clear: both;">&nbsp;</p>
        </div>
        <div class="container container-content" style="text-align: left; width: 90%; margin: 0px auto; padding: 15px; border: 1px dashed #333; background-color: #eee;">
            <p align="left">
                <?= $content ?>
            </p>
            <?php if(gmdate("m") == 11): ?>
                <p>
                    <?php if((24-gmdate("d")) < 1): ?>
                        P.S: Christmas is tomorrow!
                    <?php else: ?>
                        P.S: It's only <?=24-gmdate("d")?> days until Christmas!
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php View::factory("Email/Global/Footer")->render() ?>
        </div>
</html>