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

        <!-- Javascript -->
        <?= HTML::script("http://code.jquery.com/jquery-1.9.1.min.js"); ?>
        <?= HTML::script("http://code.jquery.com/ui/1.10.1/jquery-ui.js"); ?>
        <?= HTML::script("http://netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"); ?>
        <?= HTML::script("media/scripts/general.js"); ?>
        
        <?php foreach($scripts as $s): ?>
            <?=HTML::script($s); ?>
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
        <div class="container container-content">
            <div class="content">
                <?php if($_account->loaded()): ?>
                    <ul class="nav nav-pills">
                        <li class="<?= !strcasecmp($_area . "/" . $_controller . "/" . $_action, "SSO/MANAGE/DISPLAY") ? "active" : "" ?>">
                            <a href="<?= URL::site("sso/manage/display") ?>">Dashboard</a>
                        </li>
                        <li class="dropdown <?= !strcasecmp($_area . "/" . $_controller, "TRAINING") ? "active" : "" ?>">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                Training <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="<?= !strcasecmp($_area . "/" . $_controller . "/" . $_action, "TRAINING/CATEGORY/ADMIN_LIST") ? "active" : "" ?>">
                                    <a href="<?= URL::site("training/category/admin_list") ?>">Category Management</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown <?= !strcasecmp($_area . "/" . $_controller, "TRAINING/THEORY") ? "active" : "" ?>">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                Training :: Theory <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="<?= !strcasecmp($_area . "/" . $_controller . "/" . $_action, "TRAINING/THEORY/ADMIN_TEST_LIST") ? "active" : "" ?>">
                                    <a href="<?= URL::site("training/theory/admin_test_list") ?>">Test Management</a>
                                </li>
                                <li class="<?= !strcasecmp($_area . "/" . $_controller . "/" . $_action, "TRAINING/THEORY/QUESTION") ? "active" : "" ?>">
                                    <a href="<?= URL::site("training/theory/admin_question_list") ?>">Question Management</a>
                                </li>
                                <li class="<?= !strcasecmp($_area . "/" . $_controller . "/" . $_action, "TRAINING/THEORY/ATTEMPT") ? "active" : "" ?>">
                                    <a href="<?= URL::site("training/theory/admin_attempt_list") ?>">Attempt Management</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
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
        <div class="container container-footer">
            <div class="footer">
                <div class="row">
                    <p>
                        VATSIM-UK &copy; 2013 - 
                        <a href="http://status.vatsim-uk.co.uk" target="_blank">
                            Version <?= exec("git describe --abbrev=0 --tags") ?> (<?= gmdate("d/m/y H:i \G\M\T", filemtime(realpath(APPPATH . "../.git/"))) ?>)
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