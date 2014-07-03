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
                    <p align="left"><?= HTML::anchor("/", HTML::image("media/style/global/images/logo.png")) ?></p>
                </div>
                <div class="col-md-8 header-right">
                    <p align="right"><?= HTML::image("media/style/global/images/slogan.png"); ?></p>
                </div>
            </div>
        </div>
        <div class="container" id="mainContainer">
            <div class="container container-menu">

                <?php if ($_account->loaded()): ?>
                    <div id="menuContain" class="col-md-12 ui-corner-all">
                        <div class="menuRow container bg-warning">
                            <div class="row menuRow" id="menuRowAll">
                                <div class="col-md-2 ui-corner-all menuArea menuAreaAll">
                                    <div class="menuHeader menuHeaderAll col-md-12 ui-corner-top">My Account</div>
                                    <a href="<?= URL::site("sso/manage/display"); ?>">Dashboard</a>
                                    <a href="<?= URL::site("sso/auth/logout"); ?>">Logout</a>
                                </div>
                                <div class="col-md-2 ui-corner-all menuArea">
                                    <div class="menuHeader ui-corner-top">Training System</div>
                                    <a href="<?= URL::site("training/category/admin_list"); ?>">Manage Categories</a>
                                </div>
                                <div class="col-md-2 ui-corner-all menuArea">
                                    <div class="menuHeader ui-corner-top">Theory System</div>
                                    <a href="<?= URL::site("training/theory_test_admin/list"); ?>">Manage Tests</a>
                                    <a href="<?= URL::site("training/theory_question_admin/list"); ?>">Question Bank</a>
                                    <a href="<?= URL::site("training/theory_attempt_admin/history"); ?>" class="disabled">Attempt History</a>
                                </div>
                            </div>
                        </div>

                        <!--<div class="menuRow">
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Members</div>
                                <a href="#">Dashboard</a>
                                <a href="#">My Account</a>
                                <a href="#">Membership</a>
                                <a href="#">Messages</a>
                                <a href="#">Signature</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Activities</div>
                                <a href="#">New Activity</a>
                                <a href="#">My Availability</a>
                                <a href="#">My Activities</a>
                                <a href="#">Calendar</a>
                                <a href="#">TeamSpeak</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Training</div>
                                <a href="#">My Status</a>
                                <a href="#">Available Courses</a>
                                <a href="#">Contact</a>
                                <a href="#" class="disabled">Students</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Theory</div>
                                <a href="#">Active Material</a>
                                <a href="#">All Material</a>
                                <a href="#">Exams</a>
                                <a href="#" class="disabled">Students</a>
                            </div>
                            <div class="menuArea ui-corner-all">
                                <div class="menuHeader ui-corner-top">Practical</div>
                                <a href="#">My Sessions</a>
                                <a href="#">History</a>
                                <a href="#">Exams</a>
                                <a href="#" class="disabled">Students</a>
                            </div>
                            <div class="clearer"></div>
                        </div>-->
                        <p align="right" style="font-size: 11px;">
                            <input type="checkbox" id="staticMenuToggle" value="1" />&nbsp;&nbsp;<span id="staticMenuText">Static?</span>
                        </p>
                    </div>

                    <div id="menuToggle" class="col-md-1 col-md-offset-5 ui-state-highlight ui-corner-bottom">
                        <div id="menuIcon" class="ui-icon ui-icon-carat-1-s"></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="container container-content">
                <div class="content">
                    <div class="content-inner">
                        <h1><?= $_title ?></h1>

                        <?php if (isset($_messages) && isset($_messages["error"])): ?>
                            <?php foreach ($_messages["error"] as $error): ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <strong><?= $error->title ?></strong>
                                    <p><?= $error->message ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (isset($_messages) && isset($_messages["success"])): ?>
                            <?php foreach ($_messages["success"] as $msg): ?>
                                <div class="alert alert-success">
                                    <strong><?= $msg->title ?></strong>
                                    <p><?= $msg->message ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?= $_content ?>
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