<?php

namespace Enums\Account\Security;

class Low extends \Enums\Account\Security {
    const MIN_LIFE = 180;
    const MIN_LENGTH = 5;
    const MIN_ALPHA = 3;
    const MIN_NUMERIC = 1;
    const MIN_NON_ALPHANUM = 0;
    const NO_DUPLICATES = true;
}