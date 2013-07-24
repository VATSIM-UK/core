<?php

defined('SYSPATH') or die('No direct script access.');

class Enum_Account_Security_Member extends Enum_Account_Security {
    const MIN_LIFE = 45;
    const MIN_LENGTH = 7;
    const MIN_ALPHA = 2;
    const MIN_NUMERIC = 2;
    const MIN_NON_ALPHANUM = 1;
    const NO_DUPLICATES = true;
}