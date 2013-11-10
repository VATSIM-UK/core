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
            <p align="left" style="border-top: 1px dashed black;">
                This email was automatically sent from our system at <?=gmdate("H:i:s D jS M Y")?> GMT.<br />
                Please <a href="mailto:web-support@vatsim-uk.co.uk">let us know</a> if it's not been sent to the correct person, or displays incorrectly.
            </p>
        </div>
</html>