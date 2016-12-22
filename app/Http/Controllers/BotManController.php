<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Mpociot\BotMan\BotMan;
use App\Conversations\ExampleConversation;

class BotManController extends BaseController
{
    /**
     * Place your BotMan logic here.
     */
    public function handle()
    {
        $botman = app("botman");
        $botman->verifyServices('secret_slack_verify');

        $payload = Collection::make(json_decode(request()->getContent(), true));

        dd($payload);

        $botman->listen();
    }

    /**
     * Loaded through routes/botman.php.
     *
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {
        $bot->startConversation(new ExampleConversation());
    }
}
