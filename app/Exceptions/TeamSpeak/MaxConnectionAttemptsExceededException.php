<?php

namespace App\Exceptions\TeamSpeak;

use Exception;

class MaxConnectionAttemptsExceededException extends Exception
{
    protected $message = 'Max connection attempts exceeded';

    protected $code;

    /**
     * MaxConnectionAttemptsExceeded constructor.
     *
     * @param  string  $attempts  The number of connection attempts made. This is used as the exception code.
     */
    public function __construct($attempts, Exception $previous = null)
    {
        parent::__construct($this->message, $attempts, $previous);
    }
}
