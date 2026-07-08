<?php

namespace Database\Seeders;

use App\Models\Atc\Position;
use App\Models\Discord\DiscordRoleRule;
use Illuminate\Database\Seeder;

class DiscordRoleRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discordRoleId = config('services.discord.enroute_controller_role_id');

        DiscordRoleRule::firstOrCreate(
            ['name' => 'Solo Enroute Controller'],
            [
                'discord_id' => $discordRoleId,
                'position_type' => Position::TYPE_ENROUTE,
            ]
        );

        $this->command->info('Discord role rules seeded.');
    }
}
