<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.2" />
        <title><?= $config_site_title ?></title>

        <!-- CSS -->
        <?php foreach ($styles as $style): ?>
            <?= HTML::style($style) ?>
        <?php endforeach; ?>

        <!-- Javascript -->
        <?php foreach ($scripts as $script): ?>
            <?= HTML::script($script) ?>
        <?php endforeach; ?>
        <?= isset($_ajax_functions) ? $_ajax_functions : "" ?>
    </head>

    <body>
        <div class="wrapper">
            <div class="container container-header">
                <div class="header-left">
                    <img src="<?= URL::site("media/style/images/logo.png") ?>" alt="" />
                </div>
                <div class="header-right">
                    <img style="width: 400px;" src="<?= URL::site("media/style/images/slogan.png") ?>" alt="" />
                </div>
                <div class="clearer"></div>
            </div>
            <?= Request::factory("global/navbar")->execute() ?>
            <?= Request::factory("global/breadcrumbs")->execute() ?>
            <div class="mainMenu">
                <div class="menuContain ui-corner-bottom">
                    <?= Request::factory("global/menu")->execute() ?>
                </div>
                <div class="menuOpen ui-state-highlight ui-corner-bottom">
                    <div class="ui-icon ui-icon-carat-2-n-s menuIcon"></div>
                </div>
            </div>
            
            <div class="container container-content">
                <div class="content">
                    <h1><?= $_title ?></h1>
                    <?= $_content ?>
                </div>
            </div>
        </div>
        <div class="container-footer">
            <div class="container footer-content">

            </div>
        </div>
    </body>




</html>