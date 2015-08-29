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
                    {!! HTML::image("media/style/global/images/logo.png") !!}
                </p>
                <p align="right" style="padding-top: 20px; width: 60%; float: right;">
                    {!! HTML::image("media/style/global/images/slogan.png") !!}
                </p>
            </div>
            <p style="clear: both;">&nbsp;</p>
        </div>
        <div class="container container-content" style="text-align: left; width: 90%; margin: 0px auto; padding: 15px; border: 1px dashed #333; background-color: #eee;">
            <p align="left" style="padding: 5px; font-family: 'Josefin Slab', serif;">
                {!! $emailContent !!}<br />
                <br />
                Kind regards,<br />
                <strong>VATSIM UK</strong><br />
                {!! link_to("http://www.vatsim-uk.co.uk", "http://www.vatsim-uk.co.uk") !!}<br />
                {!! link_to("http://twitter.com/vatsimuk", "@vatsimuk") !!}<br />
            </p>
            <p align="left" style="border-top: 1px dashed black; font-family: 'Josefin Slab', serif;">
                This email was automatically sent from our system at <?= gmdate("H:i:s D jS M Y") ?> GMT.<br />
                Please <a style="font-family: 'Josefin Slab', serif;" href="mailto:web-support@vatsim-uk.co.uk?subject=Erroneous automatic email QID{{ $queue->postmaster_queue_id }}&body=Error with POSTMASTER/EK-{{ $queue->postmaster_template_id }}/QID-{{ $queue->postmaster_queue_id }}">let us know</a>
                if it's not been sent to the correct person, or displays incorrectly.<br /><br />
                <small style="font-family: 'Josefin Slab', serif;">Reference: POSTMASTER/EK-{{ $queue->postmaster_template_id }}/QID-{{ $queue->postmaster_queue_id }}</small>
            </p>
        </div>
</html>