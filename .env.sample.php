<?php

return [
    "url"                  => "", // Leave blank to auto-detect.

    /** Database Config **/
    "db.mysql.host"        => "localhost",
    "db.mysql.port"        => "3306",
    "db.mysql.user"        => "user",
    "db.mysql.pass"        => "Something",
    "db.mysql.name"        => "my_database",
    "db.mysql.name.rts"    => "my_database",
    "db.mysql.prefix"      => "",
    "db.mysql.charset"     => "utf8",
    "db.mysql.collation"   => "utf8_unicode_ci",

    /** MailGun Config **/
    "mailgun.user"         => "",
    "mailgun.pass"         => "",
    "mailgun.from.address" => "",
    "mailgun.from.name"    => "",
    "mailgun.domain"       => "",
    "mailgun.secret"       => "",

    /** AutoTools Account **/
    "vatsim.cert.at.user"  => "",
    "vatsim.cert.at.pass"  => "",
    "vatsim.cert.at.div"   => "",

    /** Vatsim SSO Config **/
    'vatsimsso.cert'       => <<<EOD
-----BEGIN RSA PRIVATE KEY-----
-----END RSA PRIVATE KEY-----
EOD
    , 'vatsimsso.key'      => "",
    'vatsimsso.secret'     => "",
    'vatsimsso.return'     => "",
];

?>