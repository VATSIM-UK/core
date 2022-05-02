<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
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
        $privacc = Role::create(['name' => 'privacc', 'guard_name' => 'web', 'default' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        Role::create(['name' => 'member', 'guard_name' => 'web', 'default' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

        // Add All Permissions
        Permission::create(['id' => '1', 'name' => '*', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '2', 'name' => 'adm/dashboard', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '3', 'name' => 'adm/search', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '4', 'name' => 'adm/mship', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '5', 'name' => 'adm/mship/account', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '6', 'name' => 'adm/mship/account/*', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '7', 'name' => 'adm/mship/account/list', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '8', 'name' => 'adm/mship/account/datachanges', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '9', 'name' => 'adm/mship/account/datachanges/view', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '10', 'name' => 'adm/mship/account/*/flag/view', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '11', 'name' => 'adm/mship/account/*/flags', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '12', 'name' => 'adm/mship/account/*/roles', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '13', 'name' => 'adm/mship/account/*/roles/attach', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '14', 'name' => 'adm/mship/account/*/roles/*/detach', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '15', 'name' => 'adm/mship/account/*/note/create', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '16', 'name' => 'adm/mship/account/*/note/view', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '17', 'name' => 'adm/mship/account/*/note/filter', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '18', 'name' => 'adm/mship/account/*/notes', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '19', 'name' => 'adm/mship/account/*/receivedEmails', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '20', 'name' => 'adm/mship/account/*/security', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '21', 'name' => 'adm/mship/account/*/security/change', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '22', 'name' => 'adm/mship/account/*/security/enable', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '23', 'name' => 'adm/mship/account/*/security/reset', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '24', 'name' => 'adm/mship/account/*/security/view', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '25', 'name' => 'adm/mship/account/*/sentEmails', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '26', 'name' => 'adm/mship/account/*/timeline', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '27', 'name' => 'adm/mship/account/email/view', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '28', 'name' => 'adm/mship/account/*/impersonate', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '29', 'name' => 'adm/mship/permission', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '30', 'name' => 'adm/mship/permission/create', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '31', 'name' => 'adm/mship/permission/list', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '32', 'name' => 'adm/mship/permission/*/update', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '33', 'name' => 'adm/mship/permission/*/delete', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '35', 'name' => 'adm/mship/permission/attach', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '36', 'name' => 'adm/mship/role', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '37', 'name' => 'adm/mship/role/*/delete', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '38', 'name' => 'adm/mship/role/*/update', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '39', 'name' => 'adm/mship/role/create', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '40', 'name' => 'adm/mship/role/list', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '41', 'name' => 'adm/sys/timeline/mship', 'guard_name' => 'web', 'created_at' => '2015-02-27 22:23:51', 'updated_at' => '2015-02-27 22:23:51']);
        Permission::create(['id' => '42', 'name' => 'adm/mship/role/default', 'guard_name' => 'web', 'created_at' => '2015-02-27 23:12:57', 'updated_at' => '2015-02-27 23:12:57']);
        Permission::create(['id' => '43', 'name' => 'adm', 'guard_name' => 'web', 'created_at' => '2015-02-27 23:20:05', 'updated_at' => '2015-02-27 23:20:05']);
        Permission::create(['id' => '44', 'name' => 'adm/mship/account/own', 'guard_name' => 'web', 'created_at' => '2015-03-03 00:13:22', 'updated_at' => '2015-03-03 00:13:22']);
        Permission::create(['id' => '45', 'name' => 'teamspeak/servergroup/serveradmin', 'guard_name' => 'web', 'created_at' => '2015-03-12 21:37:27', 'updated_at' => '2015-03-12 21:37:27']);
        Permission::create(['id' => '46', 'name' => 'teamspeak/idle/extended', 'guard_name' => 'web', 'created_at' => '2015-03-12 21:37:27', 'updated_at' => '2015-03-12 21:37:27']);
        Permission::create(['id' => '47', 'name' => 'teamspeak/idle/permanent', 'guard_name' => 'web', 'created_at' => '2015-03-12 21:37:27', 'updated_at' => '2015-03-12 21:37:27']);
        Permission::create(['id' => '48', 'name' => 'adm/mship/account/*/bans', 'guard_name' => 'web', 'created_at' => '2015-12-17 23:34:51', 'updated_at' => '2018-10-08 18:15:41']);
        Permission::create(['id' => '49', 'name' => 'adm/mship/account/*/ban/add', 'guard_name' => 'web', 'created_at' => '2015-12-17 23:34:51', 'updated_at' => '2015-12-17 23:34:51']);
        Permission::create(['id' => '50', 'name' => 'adm/mship/ban/*/modify', 'guard_name' => 'web', 'created_at' => '2015-12-17 23:34:51', 'updated_at' => '2016-01-14 00:28:13']);
        Permission::create(['id' => '51', 'name' => 'adm/mship/account/*/ban/view', 'guard_name' => 'web', 'created_at' => '2015-12-17 23:34:51', 'updated_at' => '2015-12-17 23:34:51']);
        Permission::create(['id' => '52', 'name' => 'adm/mship/ban/*/repeal', 'guard_name' => 'web', 'created_at' => '2015-12-17 23:34:51', 'updated_at' => '2016-01-14 00:28:13']);
        Permission::create(['id' => '53', 'name' => 'teamspeak/servergroup/divisionstaff', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '54', 'name' => 'teamspeak/servergroup/webstaff', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '55', 'name' => 'teamspeak/servergroup/rtsm', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '56', 'name' => 'teamspeak/servergroup/leadmentor', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '57', 'name' => 'teamspeak/servergroup/atcstaff', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '58', 'name' => 'teamspeak/servergroup/ptdstaff', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '59', 'name' => 'teamspeak/servergroup/member', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '60', 'name' => 'teamspeak/channel/essex', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '61', 'name' => 'teamspeak/channel/heathrow', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '62', 'name' => 'teamspeak/channel/egtt', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '63', 'name' => 'teamspeak/channel/northern', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '64', 'name' => 'teamspeak/channel/scottish', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '65', 'name' => 'teamspeak/channel/serts', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '66', 'name' => 'teamspeak/channel/swrts', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '67', 'name' => 'teamspeak/channel/military', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '68', 'name' => 'teamspeak/channel/pilot', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:36', 'updated_at' => '2016-07-31 19:04:36']);
        Permission::create(['id' => '70', 'name' => 'adm/system/module', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:37', 'updated_at' => '2016-07-31 19:04:37']);
        Permission::create(['id' => '71', 'name' => 'adm/system/module/*/enable', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:37', 'updated_at' => '2016-07-31 19:04:37']);
        Permission::create(['id' => '72', 'name' => 'adm/system/module/*/disable', 'guard_name' => 'web', 'created_at' => '2016-07-31 19:04:37', 'updated_at' => '2016-07-31 19:04:37']);
        Permission::create(['id' => '73', 'name' => 'adm/mship/account/all', 'guard_name' => 'web', 'created_at' => '2016-08-01 16:24:25', 'updated_at' => '2016-08-01 16:24:25']);
        Permission::create(['id' => '74', 'name' => 'adm/mship/account/active', 'guard_name' => 'web', 'created_at' => '2016-08-01 16:24:25', 'updated_at' => '2016-08-01 16:24:25']);
        Permission::create(['id' => '75', 'name' => 'adm/mship/account/division', 'guard_name' => 'web', 'created_at' => '2016-08-01 16:24:25', 'updated_at' => '2016-08-01 16:24:25']);
        Permission::create(['id' => '76', 'name' => 'adm/mship/account/nondivision', 'guard_name' => 'web', 'created_at' => '2016-08-01 16:24:25', 'updated_at' => '2016-08-01 16:24:25']);
        Permission::create(['id' => '77', 'name' => 'adm/visit-transfer', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2017-01-02 16:50:08']);
        Permission::create(['id' => '78', 'name' => 'adm/visit-transfer/dashboard', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '79', 'name' => 'adm/visit-transfer/facility', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '80', 'name' => 'adm/visit-transfer/facility/create', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-09-02 12:18:02']);
        Permission::create(['id' => '81', 'name' => 'adm/visit-transfer/facility/*/update', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-09-02 13:24:10']);
        Permission::create(['id' => '82', 'name' => 'adm/visit-transfer/application', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '83', 'name' => 'adm/visit-transfer/application/open', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '84', 'name' => 'adm/visit-transfer/application/closed', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '85', 'name' => 'adm/visit-transfer/application/review', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '86', 'name' => 'adm/visit-transfer/application/accepted', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '87', 'name' => 'adm/visit-transfer/application/completed', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '88', 'name' => 'adm/visit-transfer/application/*', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '89', 'name' => 'adm/visit-transfer/application/*/accept', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '90', 'name' => 'adm/visit-transfer/application/*/reject', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '91', 'name' => 'adm/visit-transfer/reference', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '92', 'name' => 'adm/visit-transfer/reference/pending-submission', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '93', 'name' => 'adm/visit-transfer/reference/submitted', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '94', 'name' => 'adm/visit-transfer/reference/under-review', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '95', 'name' => 'adm/visit-transfer/reference/accepted', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '96', 'name' => 'adm/visit-transfer/reference/rejected', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '97', 'name' => 'adm/visit-transfer/reference/*', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '98', 'name' => 'adm/visit-transfer/reference/*/accept', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '99', 'name' => 'adm/visit-transfer/reference/*/reject', 'guard_name' => 'web', 'created_at' => '2016-08-22 22:51:20', 'updated_at' => '2016-08-22 22:51:20']);
        Permission::create(['id' => '101', 'name' => 'adm/visit-transfer/application/*/check/met', 'guard_name' => 'web', 'created_at' => '2016-09-02 18:42:06', 'updated_at' => '2016-09-02 19:34:21']);
        Permission::create(['id' => '102', 'name' => 'adm/visit-transfer/application/*/check/not-met', 'guard_name' => 'web', 'created_at' => '2016-09-16 22:25:24', 'updated_at' => '2016-09-16 22:25:24']);
        Permission::create(['id' => '103', 'name' => 'adm/mship/account/*/bans/*', 'guard_name' => 'web', 'created_at' => '2016-09-19 21:41:31', 'updated_at' => '2016-09-19 21:44:39']);
        Permission::create(['id' => '105', 'name' => 'adm/mship/account/*/datachanges', 'guard_name' => 'web', 'created_at' => '2016-12-13 19:28:05', 'updated_at' => '2016-12-13 19:28:05']);
        Permission::create(['id' => '106', 'name' => 'adm/mship/account/*/datachanges/view', 'guard_name' => 'web', 'created_at' => '2016-12-13 19:29:17', 'updated_at' => '2016-12-13 19:29:17']);
        Permission::create(['id' => '108', 'name' => 'teamspeak/servergroup/divisioninstructor', 'guard_name' => 'web', 'created_at' => '2017-02-12 21:18:22', 'updated_at' => '2017-02-12 21:18:22']);
        Permission::create(['id' => '110', 'name' => 'adm/mship/ban/*/comment', 'guard_name' => 'web', 'created_at' => '2017-02-26 15:21:16', 'updated_at' => '2017-02-26 15:22:54']);
        Permission::create(['id' => '111', 'name' => 'teamspeak/servergroup/globalmoderator', 'guard_name' => 'web', 'created_at' => '2017-03-08 18:49:20', 'updated_at' => '2017-03-08 18:49:20']);
        Permission::create(['id' => '112', 'name' => 'teamspeak/servergroup/bogecfounder', 'guard_name' => 'web', 'created_at' => '2017-06-11 18:24:18', 'updated_at' => '2017-06-11 18:24:18']);
        Permission::create(['id' => '113', 'name' => 'adm/mship/feedback', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '114', 'name' => 'adm/mship/feedback/list', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '115', 'name' => 'adm/mship/feedback/list/atc', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '116', 'name' => 'adm/mship/feedback/list/pilot', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '117', 'name' => 'adm/mship/feedback/view/*', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '118', 'name' => 'adm/mship/feedback/configure/*', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '119', 'name' => 'adm/mship/feedback/view/*/action', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '120', 'name' => 'adm/mship/feedback/view/*/unaction', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '121', 'name' => 'adm/mship/feedback/view/*/reporter', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '123', 'name' => 'teamspeak/servergroup/marketingstaff', 'guard_name' => 'web', 'created_at' => '2017-07-31 22:24:03', 'updated_at' => '2018-04-05 17:41:50']);
        Permission::create(['id' => '125', 'name' => 'adm/mship/feedback/list/*', 'guard_name' => 'web', 'created_at' => '2017-09-03 23:08:32', 'updated_at' => '2017-09-03 23:08:32']);
        Permission::create(['id' => '126', 'name' => 'adm/mship/feedback/list/group', 'guard_name' => 'web', 'created_at' => '2017-06-25 13:25:35', 'updated_at' => '2017-06-25 13:25:35']);
        Permission::create(['id' => '128', 'name' => 'teamspeak/servergroup/communitymanager', 'guard_name' => 'web', 'created_at' => '2017-12-01 20:50:50', 'updated_at' => '2017-12-01 20:50:50']);
        Permission::create(['id' => '129', 'name' => 'adm/mship/feedback/view/group', 'guard_name' => 'web', 'created_at' => '2017-12-16 20:34:13', 'updated_at' => '2017-12-16 20:34:13']);
        Permission::create(['id' => '130', 'name' => 'adm/visit-transfer/application/*/complete', 'guard_name' => 'web', 'created_at' => '2018-01-12 20:39:06', 'updated_at' => '2018-01-12 20:39:42']);
        Permission::create(['id' => '131', 'name' => 'adm/mship/account/*/feedback', 'guard_name' => 'web', 'created_at' => '2018-01-12 22:29:19', 'updated_at' => '2018-01-12 22:29:19']);
        Permission::create(['id' => '132', 'name' => 'adm/mship/feedback/list/atcmentor', 'guard_name' => 'web', 'created_at' => '2018-02-12 19:28:32', 'updated_at' => '2018-02-12 19:28:32']);
        Permission::create(['id' => '133', 'name' => 'adm/mship/feedback/configure/atcmentor', 'guard_name' => 'web', 'created_at' => '2018-02-15 20:52:38', 'updated_at' => '2018-02-15 20:52:41']);
        Permission::create(['id' => '135', 'name' => 'adm/mship/bans', 'guard_name' => 'web', 'created_at' => '2018-02-26 23:59:53', 'updated_at' => '2018-02-26 23:59:53']);
        Permission::create(['id' => '136', 'name' => 'adm/smartcars', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '137', 'name' => 'adm/smartcars/aircraft', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '138', 'name' => 'adm/smartcars/aircraft/create', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '139', 'name' => 'adm/smartcars/aircraft/update', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '140', 'name' => 'adm/smartcars/aircraft/delete', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '141', 'name' => 'adm/smartcars/airports', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '142', 'name' => 'adm/smartcars/airports/create', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '143', 'name' => 'adm/smartcars/airports/update', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '144', 'name' => 'adm/smartcars/airports/delete', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '145', 'name' => 'adm/smartcars/exercises', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '146', 'name' => 'adm/smartcars/exercises/create', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '147', 'name' => 'adm/smartcars/exercises/update', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '148', 'name' => 'adm/smartcars/exercises/delete', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '149', 'name' => 'adm/smartcars/flights', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '150', 'name' => 'adm/smartcars/flights/override', 'guard_name' => 'web', 'created_at' => '2018-02-28 21:05:11', 'updated_at' => '2018-02-28 21:05:11']);
        Permission::create(['id' => '152', 'name' => 'adm/mship/feedback/list/*/export', 'guard_name' => 'web', 'created_at' => '2018-03-04 18:51:10', 'updated_at' => '2018-03-04 18:51:46']);
        Permission::create(['id' => '154', 'name' => 'teamspeak/servergroup/tgncmanager', 'guard_name' => 'web', 'created_at' => '2018-04-05 17:48:56', 'updated_at' => '2018-04-05 17:48:56']);
        Permission::create(['id' => '155', 'name' => 'adm/mship/feedback/new', 'guard_name' => 'web', 'created_at' => '2018-05-14 17:52:38', 'updated_at' => '2018-05-14 17:52:38']);
        Permission::create(['id' => '156', 'name' => 'adm/mship/feedback/toggle', 'guard_name' => 'web', 'created_at' => '2018-05-16 19:05:45', 'updated_at' => '2018-05-16 19:05:45']);
        Permission::create(['id' => '157', 'name' => 'adm/mship/feedback/configure/eve/*', 'guard_name' => 'web', 'created_at' => '2018-05-16 19:08:23', 'updated_at' => '2018-05-16 19:08:23']);
        Permission::create(['id' => '158', 'name' => 'adm/mship/feedback/configure/liv/*', 'guard_name' => 'web', 'created_at' => '2018-05-28 16:43:48', 'updated_at' => '2018-05-28 16:43:48']);
        Permission::create(['id' => '160', 'name' => 'adm/atc', 'guard_name' => 'web', 'created_at' => '2018-06-05 21:51:54', 'updated_at' => '2018-06-05 21:51:54']);
        Permission::create(['id' => '161', 'name' => 'adm/atc/endorsement', 'guard_name' => 'web', 'created_at' => '2018-06-05 21:51:54', 'updated_at' => '2018-06-05 21:51:54']);
        Permission::create(['id' => '162', 'name' => 'adm/mship/feedback/view/*/send', 'guard_name' => 'web', 'created_at' => '2018-08-01 20:40:35', 'updated_at' => '2018-08-01 20:40:35']);
        Permission::create(['id' => '163', 'name' => 'adm/ops', 'guard_name' => 'web', 'created_at' => '2018-08-01 20:40:35', 'updated_at' => '2018-08-01 20:40:35']);
        Permission::create(['id' => '164', 'name' => 'adm/ops/qstats', 'guard_name' => 'web', 'created_at' => '2018-08-01 20:40:35', 'updated_at' => '2018-08-01 20:40:35']);
        Permission::create(['id' => '165', 'name' => 'adm/visit-transfer/hours/*', 'guard_name' => 'web', 'created_at' => '2018-08-01 20:46:35', 'updated_at' => '2018-08-01 20:48:17']);
        Permission::create(['id' => '166', 'name' => 'adm/visit-transfer/hours', 'guard_name' => 'web', 'created_at' => '2018-08-01 20:48:12', 'updated_at' => '2018-08-01 20:48:12']);
        Permission::create(['id' => '167', 'name' => 'teamspeak/servergroup/atcmentor', 'guard_name' => 'web', 'created_at' => '2018-08-05 15:20:16', 'updated_at' => '2018-08-05 15:20:16']);
        Permission::create(['id' => '168', 'name' => 'teamspeak/servergroup/ptdmentor', 'guard_name' => 'web', 'created_at' => '2018-08-05 15:32:57', 'updated_at' => '2018-08-05 15:32:57']);
        Permission::create(['id' => '169', 'name' => 'adm/mship/feedback/view/atc/send', 'guard_name' => 'web', 'created_at' => '2018-08-11 14:33:02', 'updated_at' => '2018-08-11 14:33:02']);
        Permission::create(['id' => '170', 'name' => 'adm/mship/feedback/view/atcmentor/send', 'guard_name' => 'web', 'created_at' => '2018-08-11 14:33:20', 'updated_at' => '2018-08-11 14:33:20']);
        Permission::create(['id' => '173', 'name' => 'adm/mship/ban', 'guard_name' => 'web', 'created_at' => '2018-10-08 18:22:36', 'updated_at' => '2018-10-08 18:22:36']);
        Permission::create(['id' => '174', 'name' => 'adm/mship/feedback/view/own/', 'guard_name' => 'web', 'created_at' => '2018-10-29 23:24:44', 'updated_at' => '2018-10-29 23:24:44']);

        $privacc->givePermissionTo(['*']);
    }
}
