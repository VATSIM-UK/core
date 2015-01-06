<?php

session_start();

require_once "/var/www/sharedResources/SSO.class.php";

$SSO = new SSO("vuk.dev", "53924166b98c0", "3e7fcffaf4a26b1b6f37623405bfe53a", "http://dev.vatsim-uk.co.uk/ALawrence/corel/display.php", "http://dev.vatsim-uk.co.uk/ALawrence/corel/sso");
$SSO->authenticate();
print "<pre>" . print_r($SSO->member, true) . "</pre>";

?>
