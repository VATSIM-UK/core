<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
    <style>
      @import url(https://fonts.googleapis.com/css?family=Roboto:400,700);
      [style*="Roboto"] {
          font-family: 'Roboto', Arial, sans-serif !important
      }
    </style>
</head>

<body bgcolor="#f5f5f5">
    <style type="text/css">
        /* GLOBAL */
        * {
            margin:0;
            padding:0;
            font-family: Arial, sans-serif, 'Roboto';
            line-height: 1.6;
        }

        img {
            max-width: 100%;
        }

        body {
            -webkit-font-smoothing:antialiased;
            -webkit-text-size-adjust:none;
            width: 100%!important;
            height: 100%;
        }
        a{
            color: #00b0f0;
        }
        a:visited{
            color: #00b0f0;
        }
        a:hover{
            color: #17375e;
        }


        /* BODY */
        table.body-wrap {
            width: 100%;
            padding: 20px;
        }

        table.body-wrap .container{
            border: 1px solid #f0f0f0;
        }


        /* FOOTER */
        table.footer-wrap {
            width: 100%;
            clear:both!important;
        }

        .footer-wrap .container p {
            font-size:12px;
            color:#666;

        }

        table.footer-wrap a{
            color: #999;
        }


        /* TEXT */
        h1,h2,h3{
            font-family: Arial, sans-serif, 'Roboto';
            line-height: 1.1;
            margin-bottom:15px;
            color:#000;
            margin: 40px 0 10px;
            line-height: 1.2;
            font-weight:200;
        }

        h1 {
            font-size: 36px;
        }
        h2 {
            font-size: 28px;
        }
        h3 {
            font-size: 22px;
        }

        p, ul {
            margin-bottom: 10px;
            font-weight: normal;
            font-size:14px;
        }

        ul li {
            margin-left:5px;
            list-style-position: inside;
        }

        /* RESPONSIVENE STYLING */

        .container {
            display:block!important;
            margin:0 auto!important; /* makes it centered */
            clear:both!important;
        }

        /* Set the padding on the td rather than the div for Outlook compatibility */
        .body-wrap .container{
            padding:20px;
        }

        .content {
            margin:0 auto;
            display:block;
        }

        .content table {
            width: 100%;
        }

        #panels{
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 10px;
            background: #eeeeed;
        }

    </style>
    <table class="body-wrap" cellspacing="0">
        <tr>
            <td></td>
            <td class="container" bgcolor="#FFFFFF">
                <div class="content">
                    <table class="deviceWidth" style="background-color: rgb(23, 55, 94); width:100%" align="center" bgcolor="#17375e" border="0" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td style="padding: 20px 15px 0 15px; background-color: rgb(23, 55, 94);" bgcolor="#17375e">
                                    <a href="{{ \URL::to('/') }}"><img class="logo" width="200" style="border-radius: 0px; display: block;" alt="" src="{!! asset('assets/images/vatsim_uk_logo.png') !!}" unselectable="on" border="0"></a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 15px; background-color: rgb(23, 55, 94); border-bottom: 5px solid #00b0f0;font-family: Arial, sans-serif, 'Roboto';" bgcolor="#17375e"><small><i style="color:rgb(205, 205, 205);">{{ $subject }}</i></small></td>
                            </tr>
                        </tbody>
                    </table>
                    <table>
                        <tr>
                            <td id="panels" style="padding-left: 10px;padding-right: 10px;padding-top: 10px;background: #eeeeed;" bgcolor="#eeeeed">
                                <table class="deviceWidth" style="padding: 0 8px 0 0;" align="center" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="margin-top:10px; background-color: #17375e;border-color: #17375e;color: #fff;border-top-left-radius: 3px;border-top-right-radius: 3px;border: none !important;padding: 10px 15px;border-bottom: 1px solid transparent;font-family: Arial, sans-serif, 'Roboto';">
                                                {{ $subject }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none !important;padding: 10px 20px;background-color: white;font-family: Arial, sans-serif, 'Roboto';color: black;">
                                                @yield('content', "No content to display")
                                                <p>
                                                    Kind regards,<br>
                                                    <strong>VATSIM UK</strong>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="height:20px">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="deviceWidth" style="padding: 0 8px 10px 0;" align="center" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="background-color: #a6a6a6;border-color: #17375e;color: #fff;border-top-left-radius: 3px;border-top-right-radius: 3px;padding: 10px 15px;border-bottom: 1px solid transparent;font-family: Arial, sans-serif, 'Roboto';">
                                                Notice
                                            </td>
                                        </tr>
                                        <tr>
                                            <td  style="border: none !important;padding: 10px 20px;background-color: white;font-family: Arial, sans-serif, 'Roboto';color: black;">
                                                <p>
                                                    This email was automatically sent from our system at <?= gmdate("H:i:s D jS M Y") ?> GMT. Do not reply directly to this email, as the address is not monitored.<br>
                                                    Please <a href="mailto:web-support@vatsim-uk.co.uk?subject=Erroneous automatic email" data-toggle="tooltip" title="Report Error" style="color: #00b0f0;">let us know</a> if it's not been sent to the correct person, or displays incorrectly.
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <table class="deviceWidth" style="width:100%" align="center" border="0" cellpadding="0" cellspacing="0">
                        <tbody>
                            <tr>
                                <td style="padding: 5px 10px;" bgcolor="#363636">
                                    <table class="deviceWidth" width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table class="deviceWidth" width="100%" border="0" cellpadding="10" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <td class="center" style="text-align: left; color: rgb(241, 241, 241); line-height: 22px; padding-top: 5px; font-family: &quot;Lucida Sans Unicode&quot;, &quot;Lucida Grande&quot;, sans-serif; font-size: 11px; font-weight: normal; vertical-align: top;" valign="top">
                                                                    <a href="https://twitter.com/vatsimuk" style="text-decoration: none;">
                                                                        <img title="Twitter" alt="Twitter" src="{!! asset('assets/images/twitter.png') !!}" unselectable="on" border="0" width="50">
                                                                    </a>
                                                                    <a href="https://vatsim.uk"><img title="VATSIM UK Website" alt="VATSIM UK Website" src="{!! asset('assets/images/earth.png') !!}" unselectable="on" border="0" width="33"></a>
                                                                </td>
                                                                <td class="center" style="color: rgb(153, 153, 153); padding: 10px; font-family: &quot;Lucida Sans Unicode&quot;, &quot;Lucida Grande&quot;, sans-serif; font-size: 11px;text-align: right;" valign="top">
                                                                    <a href="{{ \URL::to('/') }}"><img class="logo" width="200" style="float:right;border-radius: 0px; display: block;" alt="" src="{!! asset('assets/images/vatsim_uk_logo.png') !!}" unselectable="on" border="0"></a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td></td>
        </tr>
    </table>
</body>
</html>
