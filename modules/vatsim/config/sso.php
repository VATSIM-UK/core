<?php defined('SYSPATH') OR die('No direct access allowed.');

/*
 * The key for whic temporary (token) details for each user will be stored e.g. $_SESSION['mykey']
 * If you chose to handle the tokens yourself by another method, you can remove this
 */
define('SSO_SESSION', 'oauth');

$cert = <<<EOD
-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAukK3aaNmqMvqNi81hCPKrxwlNo8SBgzxHFsThkPq3OK4Lo/0
EvLsM1UBnTZdVasKgnmHCU+RYE22BD3y6zYpTVyF/z/oIuyBTkFjI/RlLuTl2Tiq
SYE8TsBYLU3t/J4dih4br81tPDzmKD4sNbJnD1RrepwkyEJdJrBVoy/bWu8FRifG
fjPQ/5PKY99AdD+0Ijmgb30RVJii1PmAYuWn5HW7YzE3rlsvuB1YBJwbdt/hujBT
s8fyvruknON0qtbfzZ74h46gnwxGaaPVb7Wq/iFKwuX+URP+9YrXBSC65OkcsSLw
0SDn1x82KyixC7HEbkLCKY1vtJxFJeG2RtKgZwIDAQABAoIBAFRoFXvuy3/k4J9r
Z2IZYilWpNqDnVRWmxERiNiIgK9Wosae/6lyom1ksVWQJF3FI/8amYIzp5qvvLNR
a3sB0Ve4fRS93jGtYa+X3BiYER5eJnb4wdqLkUvSyGLHdPjMonBL0popir4Ijvhf
rGMtFJ0S0Z3/kn+Z62Kz+Cr6bfpXnbtDIsS//V6lWTlxhUeitRj5t6Ug0ZL7/1hx
VEVRykuHyD6j5kioemK9rb6MLz/8CEZfbIFhsyLYAFs5j9ZkUpuDiMzOfktT3CMm
yokgWL1HVK89J2KMkVLEmKi/pHbQhnyeQaUnOaPzDcU3DkVm3fDadZCPihvqHflO
bn06dNkCgYEA89NvQ45PImOb+a9lSNeANc8dxCrCltodyf0Op2xs5JAsJWu9te9U
VSE5BJg3e84kw3F5KBQKlu+XfHnOJwJPdLCFVLkDECRgFFq3DBOKTTLUxJLp8kZ3
XFadliSQEepPJzSqaltPYF9J64M/1fhQ80AU8kH6qmPl2C12G676ZssCgYEAw498
eWMmpgS371U/Mx9HLim+cS1bKb0AsVf3s77UjNcyKu5AqvPnHYU6taK7pq7L/mDs
LLqfhV6FqZZokfzu0CpLvoIRTwEsymVxU90u6QiGX0FjrYB4lpfgXyoOGAYrVpQg
NE0bsiSwZIEhajH6NC5xVhAyl/rQbItb391snVUCgYAhPQN/7Bhxka6jnPtrsKTz
sOZX5ZRdSCKjdHHY9PjrN5QtDcDw2k1exYamT7HQmMt+MSvZANJovQSjnV8zBiea
uq2SXR5DxxGp9KvbY5aI65MBsR1fP6fp2Izm+NkC+DQOHinVEdmCQ7sp9dxv6Mfw
QR4Z4FCxhajBjYD6SSItfQKBgBKHzCGsdYMRu2QgTcbIVfRX3EXZKGRCFoi3by9H
C9j0ozpSLSqFxgaUE0YWz3Ux5v6JrJdob6kxlbTGHc6HixGKkHbS2FUGJXpgNmbd
9I38Us8/4PQV0ldiuM4LrB42p3CixJJwJAmzVEuRuNrZNX4wUGj5S/H+wDUo12HV
F2+pAoGADgteRQTbeRKoLvdBABb5dxHn/f4h30VCaX9KNssY8lVafvFSSkobHkr9
nSmyIsm45Kgp7Ct01ccF5JuzdAJk1ZcqONdDS2LnPnPLyzLmPHsOqxyqlcFfloYM
mT7oPERnFKpLN+3eGhDh3Ytq+fY6OacdICQ1kDzxN7b8PAlrKmE=
-----END RSA PRIVATE KEY-----
EOD;

return array(
     'base'        => "https://cert.vatsim.net/sso/", // The location of the VATSIM OAuth interface
     'key' => "VATSIM_UK", // The consumer key for your organisation (provided by VATSIM)
     'secret'  => 's84YG.g3jX0k62_~fApm', // The secret key for your orgnisation (provided by VATSIM)
     'return'  => URL::base().'mship/auth/login/?return=true', // The URL users will be redirected to after they log in
     'method'  => "rsa", // The signing method you are using to encrypt your request signature.
     'cert' => $cert, // Your RSA **PRIVATE** key,
);

?>
