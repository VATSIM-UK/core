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

        // Create ATC Examiner Roles
        $obsExaminer = Role::firstOrCreate(['name' => 'ATC Examiner (OBS)', 'guard_name' => 'web', 'default' => false]);
        $twrExaminer = Role::firstOrCreate(['name' => 'ATC Examiner (TWR)', 'guard_name' => 'web', 'default' => false]);
        $appExaminer = Role::firstOrCreate(['name' => 'ATC Examiner (APP)', 'guard_name' => 'web', 'default' => false]);
        $ctrExaminer = Role::firstOrCreate(['name' => 'ATC Examiner (CTR)', 'guard_name' => 'web', 'default' => false]);

        // Add All Permissions
        $permissions = [
            app()->isProduction() ? null : '*',

            // Admin Access Permissions
            'admin.access',
            'horizon.access',
            'telescope.access',

            // Training Panel Permissions
            'training.access',
            'training.exams.access',
            'training.exams.setup',
            'training.exams.conduct.*',
            'training.exams.conduct.obs',
            'training.exams.conduct.twr',
            'training.exams.conduct.app',
            'training.exams.conduct.ctr',
            'training.exams.override-result',
            'training.theory.access',
            'training.theory.view.*',
            'training.theory.view.obs',
            'training.theory.view.twr',
            'training.theory.view.app',
            'training.theory.view.ctr',

            // Account Permissions
            'account.self',
            'account.view-insensitive.*',
            'account.view-sensitive.*',
            'account.edit-basic-details.*',
            'account.remove-password.*',
            'account.unlink-discord.*',
            'account.impersonate.*',
            'account.edit-roles.*',
            'account.edit-notes.*',
            'account.ban.create',
            'account.ban.edit.*',
            'account.ban.repeal.*',
            'account.note.create',
            'account.qualification.manual-upgrade.atc',

            // Permissions & Access Permissions
            'permission.view.*',
            'permission.create',
            'permission.edit.*',
            'permission.delete.*',
            'role.view.*',
            'role.create',
            'role.edit.*',
            'role.delete.*',
            'role.manage-delegates.*',

            // Visit Transfer System Permissions
            'vt.access',
            'vt.facility.view.*',
            'vt.facility.create',
            'vt.facility.update.*',
            'vt.application.view.*',
            'vt.application.accept.*',
            'vt.application.reject.*',
            'vt.application.complete.*',
            'vt.application.cancel.*',
            'vt.status.revoke',
            // 'vt.application.reference.accept.*',
            // 'vt.application.reference.reject.*',
            // 'vt.application.check.modify.*',

            // Waiting List System Permissions,
            'waiting-lists.access',
            'waiting-lists.view.*',
            'waiting-lists.view.atc',
            'waiting-lists.view.pilot',
            'waiting-lists.add-accounts.*',
            'waiting-lists.add-accounts.atc',
            'waiting-lists.add-accounts.pilot',
            'waiting-lists.add-accounts-admin.*',
            'waiting-lists.update-accounts.*',
            'waiting-lists.update-accounts.atc',
            'waiting-lists.update-accounts.pilot',
            'waiting-lists.remove-accounts.*',
            'waiting-lists.remove-accounts.atc',
            'waiting-lists.remove-accounts.pilot',
            'waiting-lists.add-flags.*',
            'waiting-lists.delete.*',
            'waiting-lists.delete.atc',
            'waiting-lists.delete.pilot',
            'waiting-lists.create',
            'waiting-lists.admin.*',
            'waiting-lists.admin.atc',
            'waiting-lists.admin.pilot',

            // Training Places Permissions
            'training-places.view.*',

            // // Feedback System Permissions
            'feedback.access',
            'feedback.view-submitter',
            'feedback.view-own',
            'feedback.view-sensitive',
            'feedback.view-type.*',
            'feedback.view-type.atc',
            'feedback.view-type.atc-mentor',
            'feedback.view-type.pilot',
            'feedback.view-type.group',
            'feedback.action',
            // 'feedback.form.create',
            // 'feedback.form.configure.*',

            // // Endorsement System Permissions
            // 'atc.endorsement.access',

            // Operations System Permissions
            'operations.access',

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
            'discord.atc.student.obs',
            'discord.atc.student.heathrow',

            'position-group.view.*',

            'endorsement.temporary.create.*',
            'endorsement.temporary.edit.*',
            'endorsement.bypass.minimumdays',
            'endorsement.bypass.maximumdays',

            'endorsement.create.*',
            'endorsement.create.permanent',
            'endorsement.create.temporary',
            'endorsement.view.*',
            'endorsement.view.solo',

            'endorsement-request.access',
            'endorsement-request.create.*',
            'endorsement-request.view.*',
            'endorsement-request.approve.*',
            'endorsement-request.reject.*',
            'roster.manage',
            'roster.restriction.create',
            'roster.restriction.remove',

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
