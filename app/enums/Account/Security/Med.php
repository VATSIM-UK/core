<?php

namespace Enums\Account\Security;

class Med extends \Enums\Account\Security {
    const MIN_LIFE = 90;
    const MIN_LENGTH = 6;
    const MIN_ALPHA = 2;
    const MIN_NUMERIC = 2;
    const MIN_NON_ALPHANUM = 0;
    const NO_DUPLICATES = true;
}