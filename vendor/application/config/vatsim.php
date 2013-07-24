<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
        'autotools_url'        => "https://cert.vatsim.net/vatsimnet/admin/",
	'autotools_url_auths'  => '{autotools_url}pwordcheck.php?authid={autotools_user}&authpassword={autotools_pass}&div={autotools_div}&id=%1$u&password=%2$s',
	'autotools_url_email'  => '{autotools_url}emailver.php?authid={autotools_user}&authpassword={autotools_pass}&div={autotools_div}&id=%1$u&email=%2$s',
	'autotools_url_divdb'  => '{autotools_url}divdbfullwpilot.php?authid={autotools_user}&authpassword={autotools_pass}&div={autotools_div}',
	'autotools_url_regdb'  => '{autotools_url}divdbfullwpilot.php?authid={autotools_user}&authpassword={autotools_pass}&reg={autotools_reg}',
	'autotools_url_ratch'  => '{autotools_url}ratch.php?authid={autotools_user}&authpassword={autotools_pass}&div={autotools_div}&rating=%1$u',
	'autotools_url_xstat'  => 'https://cert.vatsim.net/vatsimnet/idstatusint.php?cid=%1$u',
	'autotools_user'     => "400201",
	'autotools_pass'     => "dp4w67f",
	'autotools_reg'     => "EUR",
	'autotools_div'     => "GBR",
);
