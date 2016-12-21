<?php  namespace App\Console\Commands;

use App\Conversations\ExampleConversation;
use Mpociot\BotMan\BotManFactory;
use Mpociot\BotMan\Cache\LaravelCache;
use React\EventLoop\Factory;

class BotManListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botman:listen';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tell BotMan to listen with the Slack RTM API.';
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
        $app = app('app');
        $loop = Factory::create();

        $app->singleton('botman', function ($app) use ($loop) {
            return BotManFactory::createForRTM(config('services.botman', []), $loop, new LaravelCache());
        });

        require base_path('routes/botman.php');

        $loop->run();
    }
}