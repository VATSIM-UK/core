<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Security_Low extends Enum_Account_Security {
    const MIN_LIFE = 0;
    const MIN_LENGTH = 5;
    const MIN_ALPHA = 3;
    const MIN_NUMERIC = 1;
    const MIN_NON_ALPHANUM = 0;
    const NO_DUPLICATES = true;
}