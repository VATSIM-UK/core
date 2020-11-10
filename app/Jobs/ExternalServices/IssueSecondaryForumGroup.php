<?php

namespace App\Jobs\ExternalServices;

use Alawrence\Ipboard\Ipboard;
use App\Jobs\Middleware\RateLimited;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IssueSecondaryForumGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cid;
    protected $forumGroup;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    public function __construct(int $cid, int $forumGroup)
    {
        $this->cid = $cid;
        $this->forumGroup = $forumGroup;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ipboard = new Ipboard();

        require_once config('services.community.init_file');
        require_once \IPS\ROOT_PATH.'/system/Db/Db.php';

        $members = \IPS\Db::i()->select('member_id', 'core_pfields_content', ['field_12=?', $this->cid]);

        if (count($members) != 1) {
            Log::info('Unable to sync TG Forum Groups for '.$this->cid);

            return;
        }

        $ipboardUsers = [];

        foreach ($members as $member) {
            array_push($ipboardUsers, $member);
        }

        if (empty($ipboardUsers)) {
            Log::info('The array for '.$this->cid.'is empty');

            return;
        }

        $ipboardUser = $ipboard->getMemberById($ipboardUsers[0]);

        $currentPrimaryGroup = [$ipboardUser->primaryGroup->id];
        $currentSecondaryGroups = [];
        foreach ($ipboardUser->secondaryGroups as $secondaryGroup) {
            array_push($currentSecondaryGroups, $secondaryGroup->id);
        }

        // If they already have the group, don't do anything else
        if (in_array($this->forumGroup, $currentPrimaryGroup) || in_array($this->forumGroup, $currentSecondaryGroups)) {
            return;
        }

        // If they don't have the group, give it to them.
        $newSecondaryGroups = $currentSecondaryGroups;
        array_push($newSecondaryGroups, $this->forumGroup);

        $this->assignSecondaryGroups($ipboardUser->id, $newSecondaryGroups);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new RateLimited('issue-secondary-forum-group')];
    }

    private function assignSecondaryGroups(int $ipboardUser, array $secondaryGroups)
    {
        try {
            $client = new Client;
            $client->post(config('ipboard.api_url').'core/members/'.$ipboardUser.'?key='.config('ipboard.api_key'), ['form_params' => [
                'secondaryGroups' => $secondaryGroups,
            ]]);
        } catch (ClientException $e) {
            Log::info('Error trying to update the secondary groups for forum user id '.$ipboardUser);
        }
    }
}
