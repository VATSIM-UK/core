<?php

namespace App\Console\Commands;

use App;
use Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand
{
    /**
     * Log a string to STDOUT.
     * If STDOUT is piped/redirected, styling is removed.
     *
     * @param  string  $message  The string to output.
     * @param  null  $style  The styling to output.
     * @param  bool  $newline  If a new line should be returned at the end.
     */
    protected function log($message, $style = null, $newline = true)
    {
        // keep styling if output is not piped, or if we can't tell
        if (function_exists('posix_isatty')) {
            $style = posix_isatty(STDOUT) ? $style : null;
        }

        // add style tags to the output string
        $styled = $style ? "<$style>$message</$style>" : $message;

        // write the output
        $this->output->write($styled, $newline, OutputInterface::VERBOSITY_VERBOSE);
    }

    protected function getAuthorLink()
    {
        // get the current relative directory, and set the link to GitLab
        preg_match('/\/app\/Console\/Commands\/.*$/', __FILE__, $directory);
        $directory = array_get($directory, 0, '');
        if (App::environment('production')) {
            return 'https://gitlab.com/vatsim-uk/core/blob/production'.$directory;
        } else {
            return 'https://gitlab.com/vatsim-uk/core/blob/development'.$directory;
        }
    }
}
