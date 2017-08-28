<?php

namespace App\Console\Commands\Feedback;

use App\Models\Mship\Feedback\Feedback;
use App\Notifications\Mship\FeedbackSummary;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Console\Command;

class GenerateFeedbackSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:summarise {--interval=P1D : Interval spec - http://php.net/manual/en/dateinterval.construct.php}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a summary notification of recent feedback.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // interval spec: http://php.net/manual/en/dateinterval.construct.php
        $intervalSpec = strtoupper($this->option('interval'));
        $interval = new DateInterval($intervalSpec);
        $feedbackStart = Carbon::createFromTimestamp($_SERVER['REQUEST_TIME'])->second(0)->sub($interval);

        $groupedFeedback = Feedback::where('created_at', '>=', $feedbackStart)->with('form', 'form.contact')->get()
            ->groupBy(function (Feedback $feedback) {
                return $feedback->form->contact_id;
            });

        foreach ($groupedFeedback as $feedback) {
            $contact = $feedback->first()->form->contact;
            $contact->notify(new FeedbackSummary($feedbackStart, $feedback));
        }
    }
}
