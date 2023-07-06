<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Add Privacc Role and Normal Member
        $privacc = Role::firstOrCreate(['name' => 'privacc', 'guard_name' => 'web', 'default' => false]);
        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web', 'default' => true]);

        // Add All Permissions
        $permissions = [
            app()->isProduction() ? null : '*',

            // Admin Access Permissions
            'admin.access',
            'horizon.access',
            'telescope.access',

            // Account Permissions
            'account.self',
            'account.view-insensitive.*',
            'account.view-sensitive.*',
            'account.edit-basic-details.*',
            'account.impersonate.*',
            'account.edit-roles.*',
            'account.edit-notes.*',
            'account.ban.create',
            'account.ban.edit.*',
            'account.ban.repeal.*',

            // Permissions & Access Permissions
            'permission.view.*',
            'permission.create',
            'permission.edit.*',
            'permission.delete.*',
            'role.view.*',
            'role.create',
            'role.edit.*',
            'role.delete.*',

            // Visit Transfer System Permissions
            // 'vt.access',
            // 'vt.facility.view.*',
            // 'vt.facility.create',
            // 'vt.facility.update.*',
            // 'vt.application.view.*',
            // 'vt.application.accept.*',
            // 'vt.application.reject.*',
            // 'vt.application.complete.*',
            // 'vt.application.reference.accept.*',
            // 'vt.application.reference.reject.*',
            // 'vt.application.check.modify.*',

            // // Waiting List System Permissions,
            // 'waiting-lists.list.*.*',
            // 'waiting-lists.list.*.view',
            // 'waiting-lists.list.*.add-accounts',
            // 'waiting-lists.list.*.add-accounts-admin',
            // 'waiting-lists.list.*.remove-accounts',
            // 'waiting-lists.list.*.add-flags',
            // 'waiting-lists.list.*.update',
            // 'waiting-lists.create',

            // // Feedback System Permissions
            // 'feedback.access',
            // 'feedback.view-insensitive',
            // 'feedback.view-sensitive',
            // 'feedback.view-type.*',
            // 'feedback.view-type.atc',
            // 'feedback.view-type.atc-mentor',
            // 'feedback.view-type.pilot',
            // 'feedback.view-type.group',
            // 'feedback.send',
            // 'feedback.action',
            // 'feedback.unaction',
            // 'feedback.form.create',
            // 'feedback.form.configure.*',

            // // SmartCars System Permissions
            // 'smartcars.access',
            // 'smartcars.aircraft.view.*',
            // 'smartcars.aircraft.create',
            // 'smartcars.aircraft.update.*',
            // 'smartcars.aircraft.delete.*',
            // 'smartcars.airport.view.*',
            // 'smartcars.airport.create',
            // 'smartcars.airport.update.*',
            // 'smartcars.airport.delete.*',
            // 'smartcars.exercie.view.*',
            // 'smartcars.exercie.create',
            // 'smartcars.exercie.update.*',
            // 'smartcars.exercie.delete.*',
            // 'smartcars.flight.view.*',
            // 'smartcars.flight.edit.*',

            // // Endorsement System Permissions
            // 'atc.endorsement.access',

            // // Operations System Permissions
            // 'operations.access',

            // // TeamSpeak Permissions
            // 'teamspeak.servergroup.serveradmin',
            // 'teamspeak.idle.extended',
            // 'teamspeak.idle.permanent',
            // 'teamspeak.servergroup.divisionstaff',
            // 'teamspeak.servergroup.webstaff',
            // 'teamspeak.servergroup.rtsm',
            // 'teamspeak.servergroup.leadmentor',
            // 'teamspeak.servergroup.atcstaff',
            // 'teamspeak.servergroup.ptdstaff',
            // 'teamspeak.servergroup.member',
            // 'teamspeak.servergroup.divisioninstructor',
            // 'teamspeak.channel.essex',
            // 'teamspeak.channel.heathrow',
            // 'teamspeak.channel.egtt',
            // 'teamspeak.channel.northern',
            // 'teamspeak.channel.scottish',
            // 'teamspeak.channel.serts',
            // 'teamspeak.channel.swrts',
            // 'teamspeak.channel.military',
            // 'teamspeak.channel.pilot',
            // 'teamspeak.servergroup.globalmoderator',
            // 'teamspeak.servergroup.bogecfounder',
            // 'teamspeak.servergroup.marketingstaff',
            // 'teamspeak.servergroup.communitymanager',
            // 'teamspeak.servergroup.tgncmanager',
            // 'teamspeak.servergroup.atcmentor',
            // 'teamspeak.servergroup.ptdmentor',

            // // Discord Permissions
            // 'discord.member',
            // 'discord.dsg',
            // 'discord.web',
            // 'discord.moderator',
            // 'discord.memberservices',
            // 'discord.marketing',
            // 'discord.trainingmanager',
            // 'discord.atc.divisioninstructor',
            // 'discord.atc.appinstructor',
            // 'discord.atc.twrinstructor',
            // 'discord.atc.ncinstructor',
            // 'discord.atc.examiner',
            // 'discord.atc.mentor.s1',
            // 'discord.atc.mentor.s2',
            // 'discord.atc.mentor.s3',
            // 'discord.atc.mentor.c1',
            // 'discord.atc.mentor.heathrow',
            // 'discord.pilot.examiner',
            // 'discord.pilot.instructor',
            // 'discord.pilot.mentor',
            // 'discord.graphics',
            // 'discord.rostering',
            // 'discord.livestreaming',
            'discord.atc.student.obs'
        ];

        foreach ($permissions as $permission) {
            if (! $permission) {
                continue;
            }
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        try {
            $privacc->givePermissionTo('*');
        } catch (PermissionDoesNotExist $exception) {
            // It doesn't exist...
        }

        // $member->givePermissionTo('discord.member');
    }
}
