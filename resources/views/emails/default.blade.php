<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <style type="text/css">
      /* Bootstrap Styling */
      html {
        font-family: sans-serif;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        width:100%;
      }
      body {
        margin: 0;
      }
      article,
      aside,
      details,
      figcaption,
      figure,
      footer,
      header,
      hgroup,
      main,
      menu,
      nav,
      section,
      summary {
        display: block;
      }
      audio,
      canvas,
      progress,
      video {
        display: inline-block;
        vertical-align: baseline;
      }

      body{
        font-family: 'Roboto', sans-serif;
      }
      .container {
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
      }
      @media (min-width: 768px) {
        .container {
          width: 750px;
        }
      }
      @media (min-width: 992px) {
        .container {
          width: 970px;
        }
      }
      @media (min-width: 1200px) {
        .container {
          width: 1170px;
        }
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
      .panel {
        padding-top: 10px;
        border: none !important;
        padding-left: 8px;
        padding-right: 8px;
      }
      .panel-heading {
        background-color: #17375e;
        border-color: #17375e;
        color: #fff;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        padding: 10px 15px;
        border-bottom: 1px solid transparent;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
      }
      .panel-heading.panel-info {
        background-color: #a6a6a6;
        color: #fff;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        padding: 10px 15px;
        border-bottom: 1px solid transparent;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
      }
      .panel-body {
        padding: 15px;
        background-color: white;
      }
      .social-link {
        border-radius: 3px;
        border: 1px solid;
        text-align: center;
        background-color:rgb(216, 214, 214);
        padding: 3px;
        color: black !important;
        word-wrap: break-word;
        color: black;
        text-decoration: none;
        margin-right: 10px
      }
      .social-link:visited {
        color: black;
      }
    </style>
  </head>
  <body style="margin-top: 2%">
    <div class="container" >
      <div class="row" style="background-color: #17375e; border-bottom: 5px solid #00b0f0;">
        <div class="col-xs-8" style="padding:25px;">
          <img width="200px" style="padding-bottom:5px;" src="{!! asset("assets/images/vatsim_uk_logo.png") !!}" /></br>
          <i style="color:rgb(205, 205, 205);"><small>{{ $subject }}</small></i>
        </div>
      </div>
      <div class="row" style="background-color: #e3e3e3;">
        <div class="col-xs-12">
          <div class="panel">
            <div class="panel-heading">{{ $subject }}</div>
            <div class="panel-body">
              <div class="row">
                <div class="col-xs-12">
                  @yield('content', "No content to display")
                  <p>
                    Kind regards,<br />
                    <strong>VATSIM UK</strong>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="col-xs-offset-1 col-xs-10 col-md-offset-3 col-md-6">
            <div class="row">
              <div class="col-xs-12 text-center" style="text-align: center">
                <a class="social-link" href="https://twitter.com/vatsimuk">
                  @vatsimuk
                </a>
                <a class="social-link" href="https://vatsim.uk">
                  <i class="ion ion-earth"></i>
                  vatsim.uk
                </a>
              </div>
            </div>
          </div>
          <div class="panel">
            <div class="panel-heading panel-info">Notice</div>
            <div class="panel-body">
              <div class="row">
                <div class="col-xs-12">
                  This email was automatically sent from our system at <?= gmdate("H:i:s D jS M Y") ?> GMT. Do not reply directly to this email, as the address is not monitored.<br />
                  Please <a href="mailto:web-support@vatsim-uk.co.uk?subject=Erroneous automatic email"  data-toggle="tooltip" title="Report Error">let us know</a>
                  if it's not been sent to the correct person, or displays incorrectly.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
