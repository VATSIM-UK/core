<?php

namespace App\Conversations;

use Mpociot\BotMan\Answer;
use Mpociot\BotMan\Button;
use Mpociot\BotMan\Question;
use Mpociot\BotMan\Conversation;
use Illuminate\Foundation\Inspiring;

class ExampleConversation extends Conversation
{
    private $typeOfTraining = null;

    /**
     * First question.
     */
    public function askWhatTypeOfTraining()
    {
        return $this->ask("What type of training are you asking about? [Answer with *ATC* or *PILOT*]",
            function (Answer $answer) {
                $this->typeOfTraining = strtolower($answer->getText());

                if ($this->typeOfTraining == "pilot") {
                    $this->say("Starting training towards one of the many Pilot Ratings we offer in the UK is easy.");
                    $this->say("I'd suggest that you take a read of the content at https://www.vatsim-uk.co.uk/pilot-training/");
                    $this->say("(It's really important that you read the section at the bottom, titled 'The mentoring process broken down.'");
                    $this->say("You can get in touch with the pilot training team directly by emailing pilot-training@vatsim-uk.co.uk");
                } elseif ($this->typeOfTraining == "atc") {
                    $this->say("So you're looking to get hooked into providing ATC on the network?  Great news!");
                    $this->say("You can firstly take a look at https://www.vatsim-uk.co.uk/becoming-a-controller/");
                    $this->say("When you're ready to get started, follow the steps on that page to get you enrolled.");
                    $this->say("If you want to speak to the training team directly, you can email atc-training@vatsim-uk.co.uk");
                    $this->say("Be warned, once you start, it's very addictive!");
                }
            });
    }

    public function assistWithTraining()
    {
        if ($this->typeOfTraining == "pilot") {
            $this->reply("Starting training towards one of the many Pilot Ratings we offer in the UK is easy.");
            $this->say("I'd suggest that you take a read of the content at https://www.vatsim-uk.co.uk/pilot-training/");
            $this->say("(It's really important that you read the section at the bottom, titled 'The mentoring process broken down.'");
            $this->say("You can get in touch with the pilot training team directly by emailing pilot-training@vatsim-uk.co.uk");
        } elseif ($this->typeOfTraining == "atc") {
            $this->say("So you're looking to get hooked into providing ATC on the network?  Great news!");
            $this->say("You can firstly take a look at https://www.vatsim-uk.co.uk/becoming-a-controller/");
            $this->say("When you're ready to get started, follow the steps on that page to get you enrolled.");
            $this->say("If you want to speak to the training team directly, you can email atc-training@vatsim-uk.co.uk");
            $this->say("Be warned, once you start, it's very addictive!");
        }
    }

    /**
     * Start the conversation.
     */
    public function run()
    {
        $this->askWhatTypeOfTraining();
        $this->assistWithTraining();
    }
}
