<!DOCTYPE html>
<html lang="en">
     <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <meta name="viewport" content="width=device-width, initial-scale=1.2" />
          <title><?= $config_site_title ?></title>
          
          <!-- CSS -->
          <?//= HTML::style("http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.css"); ?>
          <?= HTML::style("media/style/V3/jqueryui/css/v3-custom/jquery-ui-1.10.3.custom.css"); ?>
          <?= HTML::style("media/bootstrap/css/bootstrap.min.css"); ?>
          <?= HTML::style("media/bootstrap/css/bootstrap-responsive.min.css"); ?>
          <?= HTML::style("media/style/V3/design.css"); ?>
          <?= HTML::style("media/style/V3/layout.css"); ?>
          
          <!-- Javascript -->
          <?= HTML::script("http://code.jquery.com/jquery-1.9.1.min.js"); ?>
          <?//= HTML::script("http://code.jquery.com/ui/1.10.1/jquery-ui.js"); ?>
          <?= HTML::script("media/style/V3/jqueryui/js/jquery-ui-1.10.3.custom.js"); ?>
          <?= HTML::script("media/bootstrap/js/bootstrap.min.js"); ?>
          <?= HTML::script("media/scripts/general.js"); ?>
          <?= HTML::script("media/scripts/temp.js"); ?>
          
          <?= isset($_ajax_functions) ? $_ajax_functions : "" ?>
          
     </head>
     <body>
               
          <div class="wrapper">
               <div class="container container-header">
                    <div class="header-left">
                         <img src="<?= URL::site("media/style/global/images/logo.png") ?>" alt="" />
                    </div>
                    
                    <div class="header-right">
                         <img style="width: 400px;" src="<?= URL::site("media/style/global/images/slogan.png"); ?>" alt="" />
                    </div>
                    
                    <div class="clearer"></div>
               </div>
               
               <div class="container container-navbar">
                    <?= Request::factory("global/navbar")->execute(); ?>
               </div>
               
               <div class="container">
                    <div class="mainMenu">
                         <div class="menuContain ui-corner-all">
                              <?= Request::factory("global/menu")->execute(); ?>
                         </div>
                    </div>
                    
                    <div class="menuOpen ui-state-highlight ui-corner-bottom">
                         <div class="ui-icon ui-icon-carat-1-s menuIcon"></div>
                    </div>
               </div>
               
               <div class="container container-breadcrumb">
                    <?= Request::factory("global/breadcrumbs")->execute(); ?>
               </div>
                    
               <div class="container container-content">
                    <div class="content">
                         <h1><?= $_title; ?></h1>
                         
                         <?= str_replace("\n", "\n                         ", $_content); ?>
                         
                    </div>
               </div>
                    
               
          </div>
     </body>
</html>