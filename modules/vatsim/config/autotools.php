<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(
     'autotools_url'        => "https://cert.vatsim.net/",
     'autotools_url_division' => "{autotools_url}vatsimnet/admin/",
     'autotools_url_auths'  => '{autotools_url_division}passsword_check.php?authuser={autotools_user}&authpass={autotools_pass}&cid=%3$u&passwd=%4$s',
     'autotools_url_divdb'  => '{autotools_url_division}divdb_download.php?authuser={autotools_user}&authpass={autotools_pass}&div={autotools_div}',
     'autotools_url_regdb'  => '{autotools_url_division}somewhere.php?authuser={autotools_user}&authpass={autotools_pass}&reg={autotools_reg}',
     'autotools_url_ratch'  => '{autotools_url_division}somewhere.php?authuser={autotools_user}&authpass={autotools_pass}&rating=%1$u',
     'autotools_url_xstat'  => '{autotools_url}vatsimnet/idstatusint.php?cid=%1$u',
     'autotools_url_xprat'  => '{autotools_url}vatsimnet/idstatusprat.php?cid=%1$u',
     'autotools_user'     => "vatsim-uk",
     'autotools_pass'     => "password1",
     'autotools_reg'     => "EUR",
     'autotools_div'     => "GBR",
);
