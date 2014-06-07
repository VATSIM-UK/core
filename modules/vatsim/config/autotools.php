<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
        'autotools_url'        => "https://cert.vatsim.net/vatsimnet/admin/",
        'autotools_auth'       => 'authid={auth.autotools.id}&authpassword={auth.autotools.password}',
	'autotools_url_auths'  => '{autotools_url}pwordcheck.php?{autotools_auth}&div={autotools_div}&id=%1$u&password=%2$s',
	'autotools_url_email'  => '{autotools_url}emailver.php?{autotools_auth}&div={autotools_div}&id=%1$u&email=%2$s',
	'autotools_url_divdb'  => '{autotools_url}divdbfullwpilot.php?{autotools_auth}&div={auth.autotools.division}',
	'autotools_url_regdb'  => '{autotools_url}divdbfullwpilot.php?{autotools_auth}&reg={auth.autotools.region}',
	'autotools_url_ratch'  => '{autotools_url}ratch.php?{autotools_auth}&div={auth.autotools.division}&rating=%1$u',
	'autotools_url_xstat'  => 'https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=%1$u',
);