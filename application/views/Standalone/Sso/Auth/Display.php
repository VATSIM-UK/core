<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.2" />
        <title><?= $config_site_title ?></title>

        <!-- CSS -->
        <?= HTML::style("http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.css"); ?>
        <?= HTML::style("media/bootstrap/css/bootstrap.min.css"); ?>
        <?= HTML::style("media/bootstrap/css/bootstrap-responsive.min.css"); ?>
        <?= HTML::style("media/style/Standalone/design.css"); ?>
        <?= HTML::style("http://fonts.googleapis.com/css?family=Yellowtail"); ?>

        <!-- Javascript -->
        <?= HTML::script("http://code.jquery.com/jquery-1.9.1.min.js"); ?>
        <?= HTML::script("http://code.jquery.com/ui/1.10.1/jquery-ui.js"); ?>
        <?= HTML::script("media/bootstrap/js/bootstrap.min.js"); ?>
        <?= HTML::script("media/scripts/general.js"); ?>
    </head>
    <body>
        <div class="container container-header">
            <div class="row-fluid">
                <div class="span4 header-left">
                    <p align="left"><?= HTML::image("media/style/global/images/logo.png") ?></p>
                </div>
                <div class="span8 header-right">
                    <p align="right"><?= HTML::image("media/style/global/images/slogan.png"); ?></p>
                </div>
            </div>
        </div>
        <div class="container container-content">
            <div class="content">
                <h1>Details</h1>
                <p>
                    Below are the current details stored by the Single Sign-On (SSO) system.
                    Please note that as not all data has been transitioned from other (older) systems,
                    some data might be recorded incorrectly.
                </p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong></strong>
                        <ul>
                            <li><?= $error ?></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong></strong>
                        <ul>
                            <li><?= $message ?></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <table class="table">
                    <tr>
                        <th>CID</th>
                        <td><?=$_account->id?></td>
                    </tr>
                    <tr>
                        <th>First Name</th>
                        <td><?=$_account->name_first?></td>
                    </tr>
                    <tr>
                        <th>Last Name</th>
                        <td><?=$_account->name_last?></td>
                    </tr>
                    <tr>
                        <th>Primary Email Address</th>
                        <td>
                            <strong><?=$_account->emails->get_active_primary()->email?></strong>
                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?=gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($_account->emails->get_active_primary()->created))?>">
                                <em>added <?=Date::fuzzy_span(strtotime($_account->emails->get_active_primary()->created))?></em>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Secondary Email Addresses</th>
                        <td>
                            <?php if(count($_account->emails->get_active_secondary()) > 0): ?>
                                <?php foreach($_account->emails->get_active_secondary() as $email): ?>
                                    <strong><?=$email->email?></strong>
                                    
                                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?=gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($email->created))?>">
                                        <em>added <?=Date::fuzzy_span(strtotime($email->created))?></em><br />
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                No secondary email addresses are currently set.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Second Layer Security</th>
                        <td>
                            <?php if($_account->security->loaded() && $_account->security->value != null): ?>
                                You currently have second layer security enabled.
                                <?php if($_account->security->type == Enum_Account_Security::MEMBER): ?>
                                    <strong>You are allowed to disable this.</strong>
                                <?php else: ?>
                                    <strong>You are not allowed to disable this.</strong>
                                <?php endif; ?>
                            <?php else: ?>
                                Second layer security is disabled on your account.
                            <?php endif; ?>
                            <br />
                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="
                               To protect your account further, you can add a secondary password to your account.  You will then be required to enter this password
                               after logging in, prior to being granted access to your account or our systems."><em>What is this?</em></a>
                        </td>
                    </tr>
                    <tr>
                        <th>Last SSO Login</th>
                        <td>
                            <?php if($_account->last_login_ip != 0): ?>
                                <strong><?=$_account->get_last_login_ip()?></strong>
                                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?=gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($_account->last_login))?>">
                                    <em><?=Date::fuzzy_span(strtotime($_account->last_login))?></em> 
                                </a>
                            <?php else: ?>
                                No login history available.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>ATC Qualifications</th>
                        <td> 
                            <?php foreach($_account->qualifications->get_all_atc() as $qual): ?>
                                <?=$qual->formatQualification(true)?> (<?=$qual?>)
                                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?=gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created))?>">
                                    <em>added <?=Date::fuzzy_span(strtotime($qual->created))?></em>.<br />
                                </a>
                            <?php endforeach; ?>
                            <?php if(count($_account->qualifications->get_all_atc()) < 1): ?>
                                You have no ATC ratings.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Pilot Qualifications</th>
                        <td>
                            <?php foreach($_account->qualifications->get_all_pilot() as $qual): ?>
                                <?=$qual->formatQualification(true)?> (<?=$qual?>)
                                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?=gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created))?>">
                                    <em>added <?=Date::fuzzy_span(strtotime($qual->created))?></em>.<br />
                                </a>
                            <?php endforeach; ?>
                            <?php if(count($_account->qualifications->get_all_pilot()) < 1): ?>
                                You have no Pilot ratings.
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Actions</th>
                        <td>
                            [<?=HTML::anchor("sso/auth/logout", "Logout")?>]
                            
                            <?php if($_account->security->loaded() && $_account->security->type == Enum_Account_Security::MEMBER): ?>
                                &nbsp;&nbsp;
                                [<?=HTML::anchor("sso/auth/security_disable", "Disable Second Layer Security")?>]
                            <?php elseif(!$_account->security->loaded()): ?>
                                &nbsp;&nbsp;
                                [<?=HTML::anchor("sso/auth/security_enable", "Set Second Layer Security")?>]
                            <?php endif; ?>
                            
                            <?php if(in_array($_account->id, array(980234, 1010573))): ?>
                                &nbsp;&nbsp;
                                [<?=HTML::anchor("sso/auth/override", "Account Override")?>]
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="container container-footer">
            <div class="footer">
                <div class="row-fluid">
                    <p>VATSIM-UK &copy; 2013 - Version <?=exec("git describe --abbrev=0 --tags")?></p>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript" language="javascript">
        $(".tooltip_displays").tooltip();
    </script>

</html>