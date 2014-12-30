<?php

namespace Enums\Account\Security;

class High extends \Enums\Account\Security {
    const MIN_LIFE = 45;
    const MIN_LENGTH = 7;
    const MIN_ALPHA = 2;
    const MIN_NUMERIC = 2;
    const MIN_NON_ALPHANUM = 1;
    const NO_DUPLICATES = true;
}