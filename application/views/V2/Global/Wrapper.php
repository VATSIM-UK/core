<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $title; ?></title>
        <script type="text/javascript" language="javascript">
            var g_url = "http://www.vatsim-uk.co.uk/";
            var g_rel = "/";
            var g_admin_rel = "/acp/";
            var g_ajax = g_url + g_rel + 'library/';
        </script>

        <!--Site CSS-->
        <?php echo HTML::style("media/style/V2/Site/main.css"); ?>
        <?php echo HTML::style("media/style/V2/Site/message-boxes.css"); ?>

        <!--jQuery-->
        <link type="text/css" rel="stylesheet" href="http://srv03.vatsim-uk.co.uk/sharedResources/jquery/jquery-ui-css/custom-theme/jquery-ui.custom.css" />
        <script type="text/javascript" src="http://srv03.vatsim-uk.co.uk/sharedResources/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="http://srv03.vatsim-uk.co.uk/sharedResources/jquery/jquery-ui.min.js"></script>

        <!--Custom Javascripts-->
        <?php echo HTML::script("media/scripts/V2/Site/main.js"); ?>
        <?php echo HTML::script("media/scripts/V2/Site/gmaps.js"); ?>
        <?php echo HTML::script("media/scripts/V2/Site/site/home.js"); ?>

        <!--Begin Cookie Consent plugin by Silktide-->
        <link rel="stylesheet" type="text/css" href="http://assets.cookieconsent.silktide.com/current/style.min.css"/>
        <script type="text/javascript" src="http://assets.cookieconsent.silktide.com/current/plugin.min.js"></script>
        <script type="text/javascript">
            cc.initialise({
                cookies: {
                    analytics: {},
                    necessary: {}
                },
                settings: {
                    consenttype: "implicit",
                    style: "light",
                    hideprivacysettingstab: true
                },
                strings: {
                    analyticsDefaultDescription: 'We anonymously measure your use of this website to improve your experience.  Data is collected about your hardware and your website habits, which allow us to better develop technology to suit your needs.'
                }
            });
        </script>
    </head>
    <!-- Online Controllers Map Info -->

    <body onload="runNotams('news');
            runEvents('eventsFeed');">
        <div class="top"></div>
        <div class="banner">
            <div class="bannerLeft">
                <a href="http://www.vatsim-uk.co.uk/"><?php echo HTML::image('media/style/global/images/logo.png', array('border' => 0, 'width' => '300px')); ?></a>
            </div>
            <div class="bannerRight">
                <?php echo HTML::image('media/style/V2/Site/images/tagline-blue-large.png', array('class' => 'tagLine')); ?>


                <a href="http://www.twitter.com/vatsimuk" target="_blank"><?php echo HTML::image('media/style/V2/Site/images/twitter.png', array('class' => 'bannerTwitterWrapper')); ?></a>
                <a href="http://www.facebook.com/vatsimuk" target="_blank"><?php echo HTML::image('media/style/V2/Site/images/fb.png', array('class' => 'bannerFacebookWrapper')); ?></a>
                <?php echo View::factory('V2/Global/Navigation'); ?>
            </div>
            <div class="clearBoth"></div>
        </div>
        <div class="superWrapper rtl rbr rbl">
            <div class="subWrapper rtl rbr rbl">
                <div class="colLeft rtl rbl">
                    <?php echo View::factory('/V2/Global/Sidebar'); ?>
                    
                </div> <!-- colLeft -->
                <div class="colRight">
                    <div class="contentWrapper">
                        <?php echo $_content; ?>

                    </div>
                </div> <!-- colRight -->
                <div class="clearBoth"></div>
            </div> <!-- subWrapper -->
        </div> <!-- supWrapper -->
        <div class="footerWrapper">
            <?php echo View::factory('V2/Global/Footer'); ?>
        </div>
        <script type="text/javascript" class="cc-onconsent-inline-analytics">
            var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
            document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
        </script>
        <script type="text/javascript" class="cc-onconsent-analytics">
            try {
                var pageTracker = _gat._getTracker("UA-13128412-2");
                pageTracker._trackPageview();
            } catch (err) {
            }
        </script>
    </body>
</html>