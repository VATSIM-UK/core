<?php

namespace App\Console\Commands;

use DB;
use TeamSpeak3_Node_Server;
use App\Libraries\TeamSpeak;
use App\Models\TeamSpeak\Group;
use App\Models\Mship\Permission;
use App\Models\TeamSpeak\Channel;
use App\Models\Mship\Qualification;
use App\Models\TeamSpeak\ChannelGroup;

class TeamSpeakMapper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teaman:map';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map groups and channels from TeamSpeak to the TeamSpeak tables.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('This command will truncate the group and channel tables.');
        $this->warn('You should check this command is up-to-date with the current TeamSpeak configuration before continuing.');
        if ($this->confirm('Do you wish to continue?')) {
            $tscon = TeamSpeak::run('VATSIM UK Mapper');
            $this->importGroups($tscon);
            $this->importChannels($tscon);
            $this->mapChannelGroups();
            $this->info('Tables mapped successfully.');
        } else {
            $this->error('Aborting.');
        }
    }

    protected function importGroups(TeamSpeak3_Node_Server $tscon)
    {
        $newGroupModels = [];
        $defaultServerGroup = $tscon['virtualserver_default_server_group'];
        $defaultChannelGroup = $tscon['virtualserver_default_channel_group'];
        $qualifications = Qualification::all();

        $serverGroups = $tscon->serverGroupList(['type' => 1]);
        foreach ($serverGroups as $group) {
            $qualificationId = null;
            foreach ($qualifications as $qual) {
                if (preg_match("/^{$qual->code}/", $group['name'])) {
                    $qualificationId = $qual->id;
                    continue;
                }
            }

            $permissionId = $this->getServerGroupPermission($group);

            $newGroupModels[] = [
                'dbid' => $group['sgid'],
                'name' => $group['name'],
                'type' => 's',
                'default' => $defaultServerGroup == $group['sgid'] ? 1 : 0,
                'qualification_id' => $qualificationId,
                'permission_id' => $permissionId,
            ];
        }

        $channelGroups = $tscon->channelGroupList(['type' => 1]);
        foreach ($channelGroups as $group) {
            $newGroupModels[] = [
                'dbid' => $group['cgid'],
                'name' => $group['name'],
                'type' => 'c',
                'default' => $defaultChannelGroup == $group['cgid'] ? 1 : 0,
                'qualification_id' => null,
                'permission_id' => null,
            ];
        }

        Group::truncate();
        Group::insert($newGroupModels);
    }

    protected function getServerGroupPermission($group)
    {
        $name = $group['name'];
        switch ($name) {
            case 'Server Admin':
                return Permission::where('name', 'teamspeak/servergroup/serveradmin')->first()->id;
            case 'Division Staff':
                return Permission::where('name', 'teamspeak/servergroup/divisionstaff')->first()->id;
            case 'Web Staff':
                return Permission::where('name', 'teamspeak/servergroup/webstaff')->first()->id;
            case 'RTS Manager':
                return Permission::where('name', 'teamspeak/servergroup/rtsm')->first()->id;
            case 'Lead Mentor':
                return Permission::where('name', 'teamspeak/servergroup/leadmentor')->first()->id;
            case 'ATC Staff':
                return Permission::where('name', 'teamspeak/servergroup/atcstaff')->first()->id;
            case 'PTD Staff':
                return Permission::where('name', 'teamspeak/servergroup/ptdstaff')->first()->id;
            case 'Member':
                return Permission::where('name', 'teamspeak/servergroup/member')->first()->id;
            default:
                return;
        }
    }

    protected function mapChannelGroups()
    {
        $rtsGroup = ChannelGroup::where('name', 'RTS Staff')->first();
        $pilotGroup = ChannelGroup::where('name', 'Pilot Staff')->first();
        $rtsChannels = Channel::where('name', 'Controller RTSs')->first()->children;
        $pilotChannel = Channel::where('name', 'Pilot Training')->first();

        $newModels = [];
        foreach ($rtsChannels as $channel) {
            $newModels[] = [
                'channel_id' => $channel->id,
                'channelgroup_id' => $rtsGroup->dbid,
                'permission_id' => Permission::where('display_name', 'LIKE', 'TeamSpeak / Channel / '.explode(' ', $channel->name)[0].'%')->first()->id,
            ];
        }

        $newModels[] = [
            'channel_id' => $pilotChannel->id,
            'channelgroup_id' => $pilotGroup->dbid,
            'permission_id' => Permission::where('display_name', 'LIKE', 'TeamSpeak / Channel / Pilot Training')->first()->id,
        ];

        DB::table('teamspeak_channel_group_permission')->truncate();
        DB::table('teamspeak_channel_group_permission')->insert($newModels);
    }

    protected function importChannels(TeamSpeak3_Node_Server $tscon)
    {
        $newChannelModels = [];

        $channels = $tscon->channelList();
        foreach ($channels as $channel) {
            $newChannelModels[] = [
                'id' => $channel['cid'],
                'parent_id' => $channel['pid'],
                'name' => $channel['channel_name'],
            ];
        }

        Channel::truncate();
        Channel::insert($newChannelModels);
    }
}
