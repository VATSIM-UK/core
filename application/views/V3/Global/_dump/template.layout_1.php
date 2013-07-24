<?php
/**
 * 
 * This file was written for use on vatsim-uk.co.uk (and vatsim-uk.org) only
 * and is not to be copied or distributed for use on any other site. Permission
 * to use the content of this file must come from the VATUK (Vatsim UK) Web
 * Services department plus the developer of the file.
 * 
 * Changes to this script must be approved by the script developer who reserves
 * the right to withdraw this script from all use at any time.
 * 
 * This copyright notice is to remain intact and unedited at all times
 * 
 * @author Kieran Hardern
 * @copyright Copyright (C) 2012 onwards, Kieran Hardern. All rights reserved.
 * @version 1.0
 * 
 * File:			sitefiles/layout/start.layout.php
 * Description:	Outputs the beginning of the layout file - to be called within the
 * 				layout class	
 * 
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">
    <head>
        <link rel="stylesheet" href="<?= STYLE; ?>design.css" type="text/css" />
        <link rel="stylesheet" href="<?= STYLE; ?>layout.css" type="text/css" />
        <link rel="stylesheet" href="<?= STYLE; ?>jquery/jquery.ui.all.css" type="text/css" />

        <title><?= $this->headTitle; ?></title>

        <script src="<?= SCRIPTS; ?>jquery/jquery-1.7.2.min.js" type="text/javascript"></script>	
        <script src="<?= SCRIPTS; ?>jquery/jquery-ui-1.8.22.custom.js" type="text/javascript"></script>

        <link rel="stylesheet" href="<?= STYLE; ?>jquery/jquery.ui.all.css" type="text/css" />

        <script src="<?= SCRIPTS; ?>general.js" type="text/javascript"></script>
        <script src="<?= SCRIPTS; ?>layoutTabs.js" type="text/javascript"></script>
        <script src="<?= SCRIPTS; ?>login.js" type="text/javascript"></script>
        <?php
        //output any page-specific javascripts set by the page template
        if (isset($Scripts)) {
            //create an array if this is a single script request
            if (!is_array($Scripts)) {
                $Scripts = array($Scripts);
            }
            //output the scripts, keeping the tabbing alignment
            foreach ($Scripts as $loc) {
                echo "\n\t\t" . '<script src="' . SCRIPTS . 'page/' . $loc . '.js" type="text/javascript"></script>';
            }
        }
        ?>


    </head>
    <body>
        <div class="wrapper">
            <div class="wrapper2">
                <div class="wrapper3">
                    <div class="wrapper4">
                        <div class="wrapper5">

                            <div class="container-header">
                                <div class="header-left">
                                    <img src="<?= STYLE; ?>images/logo.png" alt="" />
                                </div>
                                <div class="header-right">
                                    <img style="width: 400px;" src="<?= STYLE; ?>images/slogan.png" alt="" />
                                </div>
                                <div class="clearer"></div>
                            </div>

                            <div class="nav-wrapper">
                                <div class="nav-wrapper2">
                                    <div class="nav-left">
                                        <?php
                                        //if logged in, output the user's name and CID
                                        if ($this->{_SESSION}->isLoggedIn()) {
                                            echo $this->{_SESSION}->user->getData('name') . ' (' . $this->{_SESSION}->user->cid() . ')';
                                            //if using a login alias, output the real name and CID
                                            if ($this->{_SESSION}->user->cid() != $this->{_SESSION}->actualUser->cid()) {
                                                echo ' <em>[';
                                                echo $this->{_SESSION}->actualUser->getData('name') . ' (' . $this->{_SESSION}->actualUser->cid() . ')';
                                                echo ']</em>';
                                            }
                                        } else {
                                            echo 'You are not logged in';
                                        }
                                        ?>
                                    </div>
                                    <div class="nav-right">
                                        <a href="#">Home</a> &middot;
                                        <?php
                                        if ($this->{_SESSION}->isLoggedIn()) {
                                            echo '<a href="#" onclick="$(\'#loginBox\').dialog(\'open\');">Logout</a> &middot;';
                                        } else {
                                            echo '<a href="#" onclick="$(\'#loginBox\').dialog(\'open\');">Login</a> &middot;';
                                        }
                                        ?>

                                        <a href="#" onclick="$('#dashboardManager').dialog('open');">Modules</a> &middot;
                                        <a href="#">Home</a> &middot;
                                        <a href="#">Home</a> &middot;
                                        <a href="#">Home</a> &middot;
                                    </div>
                                    <div class="clearer"></div>
                                </div>
                            </div>
                            <?php
                            if ($this->{_SESSION}->isLoggedIn()) {
                                ?>
                                <div class="main-menu">
                                    <div class="menu-main ui-corner-bottom">
                                <?php
                                echo $this->feature('mainMenu', array(), "\t\t");
                                ?>
                                    </div>
                                    <div class="menu-open ui-state-highlight ui-corner-bottom">
                                        <div class="ui-icon ui-icon-carat-2-n-s main-icon"></div>
                                    </div>
                                </div>
    <?php
}
?>

                            <div class="content-wrapper">
                                <div class="content-wrapper2">
                                    <div class="content-wrapper3">

<?php
if ($this->pageTitle) {
    echo '<h1>' . $this->pageTitle . '</h1>';
}
?>

                                        <?php
                                        echo $this->feature('message');
                                        ?>

                                        <?php
                                        echo $MainContent;
                                        ?>

                                    </div>
                                </div>
                            </div>

                            <div class="container-footer">
                                <div class="footer-left">
                                    &copy; VATSIM-UK
                                </div>
                                <div class="footer-right">
                                    <a href="#">Footer Link</a>
                                    <a href="#">Footer Link</a>
                                    <a href="#">Footer Link</a>
                                    <a href="#">Footer Link</a>
                                    <a href="#">Footer Link</a>
                                </div>
                                <div class="clearer"></div>
                            </div>				

<?php
//TODO: Process with layout class function
include('layout/dialog.layout.php');
?>

                        </div>
                        <div class="clearer"></div>
                    </div>	
                </div>
            </div>
        </div>
    </body>
</html>

