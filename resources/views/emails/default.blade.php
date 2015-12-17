<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href='http://fonts.googleapis.com/css?family=Josefin+Slab:600' rel='stylesheet' type='text/css'>
    </head>
    <body style="font-family: 'Josefin Slab', serif;">
        <div class="container container-header">
            <div class="col-md-4 header-left">
                <p align="left" style="width: 40%; float: left;">
                    {!! HTML::image("assets/style/global/images/logo.png") !!}
                </p>
                <p align="right" style="padding-top: 20px; width: 60%; float: right;">
                    {!! HTML::image("assets/style/global/images/slogan.png") !!}
                </p>
            </div>
            <p style="clear: both;">&nbsp;</p>
        </div>
        <div class="container container-content" style="text-align: left; width: 90%; margin: 0px auto; padding: 15px; border: 1px dashed #333; background-color: #eee;">
            <p align="left" style="padding: 5px; font-family: 'Josefin Slab', serif;">
                @yield('content', "No content to display")
            </p>

            <p>
                Kind regards,<br />
                <strong>VATSIM UK</strong><br />
                {!! link_to("http://www.vatsim-uk.co.uk", "http://www.vatsim-uk.co.uk") !!}<br />
                {!! link_to("http://twitter.com/vatsimuk", "@vatsimuk") !!}<br />
            </p>

            <p align="left" style="border-top: 1px dashed black; font-family: 'Josefin Slab', serif;">
                <br />
                This email was automatically sent from our system at <?= gmdate("H:i:s D jS M Y") ?> GMT.<br />
                Please <a style="font-family: 'Josefin Slab', serif;" href="mailto:web-support@vatsim-uk.co.uk?subject=Erroneous automatic email">let us know</a>
                if it's not been sent to the correct person, or displays incorrectly.
            </p>
        </div>
</html>