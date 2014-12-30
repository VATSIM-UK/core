<?php

namespace Enums\Account\Security;

class Member extends \Enums\Account\Security {
    const MIN_LIFE = 0;
    const MIN_LENGTH = 3;
    const MIN_ALPHA = 1;
    const MIN_NUMERIC = 0;
    const MIN_NON_ALPHANUM = 0;
    const NO_DUPLICATES = false;
}