<?php

use Illuminate\Database\Migrations\Migration;

class StaffManagementData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = new DateTime();

        $department_management = DB::table('staff_positions')->insertGetId(['name' => 'Management', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_community = DB::table('staff_positions')->insertGetId(['parent_id' => $department_management, 'name' => 'Community', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_marketing = DB::table('staff_positions')->insertGetId(['parent_id' => $department_management, 'name' => 'Marketing', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining = DB::table('staff_positions')->insertGetId(['parent_id' => $department_management, 'name' => 'ATC Training', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_instructors = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining, 'name' => 'Instructors', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining, 'name' => 'RTS Managers', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_essex = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Essex', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_heathrow = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Heathrow', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_london = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'London', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_northern = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Northern', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_sconi = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Scotland & Northern Ireland', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_serts = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'South East', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_rtsms_swrts = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'South West', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining, 'name' => 'Mentors', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_essex = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Essex', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_essex_luton = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_essex, 'name' => 'London Luton (EGGW)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_essex_stansted = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_essex, 'name' => 'London Stansted (EGSS)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_heathrow = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Heathrow', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_heathrow_heathrow = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_heathrow, 'name' => 'London Heathrow (EGLL)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_london = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'London', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_london_london = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_london, 'name' => 'London Area Control', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_northern = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Northern', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_northern_birmingham = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'Birmingham (EGBB)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_northern_eastmids = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'East Midlands (EGNX)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_northern_liverpool = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'Liverpool (EGGP)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_northern_manchester = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'Manchester (EGCC)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_sconi = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Scotland & Northern Ireland', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_sconi_edinburgh = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Edinburgh (EGPH)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_sconi_glasgow = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Glasgow (EGPF)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_sconi_scottish = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Scottish Area Control', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_sconi_shanwick = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Shanwick Oceanic Control', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_serts = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'South East', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_serts_city = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_serts, 'name' => 'London City (EGLC)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_serts_gatwick = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_serts, 'name' => 'London Gatwick (EGKK)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_swrts = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'South West', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_swrts_bristol = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_swrts, 'name' => 'Bristol (EGGD)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_atctraining_mentors_swrts_jersey = DB::table('staff_positions')->insertGetId(['parent_id' => $department_atctraining_mentors_swrts, 'name' => 'Jersey (EGJJ)', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_pilottraining = DB::table('staff_positions')->insertGetId(['parent_id' => $department_management, 'name' => 'Pilot Training', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_pilottraining_instructors = DB::table('staff_positions')->insertGetId(['parent_id' => $department_pilottraining, 'name' => 'Instructors', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_pilottraining_assistants = DB::table('staff_positions')->insertGetId(['parent_id' => $department_pilottraining, 'name' => 'Assistants', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_webservices = DB::table('staff_positions')->insertGetId(['parent_id' => $department_management, 'name' => 'Web Services', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);
        $department_webservices_assistants = DB::table('staff_positions')->insertGetId(['parent_id' => $department_webservices, 'name' => 'Assistants', 'type' => 'D', 'created_at' => $date, 'updated_at' => $date]);

        DB::table('staff_positions')->insert([
            ['parent_id' => $department_management, 'name' => 'Division Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_management, 'name' => 'Deputy Division Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_community, 'name' => 'Community Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_marketing, 'name' => 'Marketing Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining, 'name' => 'ATC Training Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining, 'name' => 'Deputy ATC Training Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_instructors, 'name' => 'Senior Instructor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_instructors, 'name' => 'Instructor (Area)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_instructors, 'name' => 'Instructor (Approach & Aerodrome)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors, 'name' => 'Head Mentor (Area)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors, 'name' => 'Head Mentor (Approach & Aerodrome)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_essex_luton, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_essex_stansted, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_heathrow_heathrow, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_london_london, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_northern_birmingham, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_northern_eastmids, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_northern_liverpool, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_northern_manchester, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_sconi_edinburgh, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_sconi_glasgow, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_sconi_scottish, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_sconi_shanwick, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_serts_city, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_serts_gatwick, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_swrts_bristol, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_mentors_swrts_jersey, 'name' => 'Lead Mentor', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_essex, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_heathrow, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_london, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_northern, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_sconi, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_serts, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_atctraining_rtsms_swrts, 'name' => 'RTS Manager', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_pilottraining, 'name' => 'Pilot Training Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_pilottraining_instructors, 'name' => 'Flight Training (P1)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_pilottraining_instructors, 'name' => 'Flight Training (P2)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_pilottraining_instructors, 'name' => 'Flight Training (P3)', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_pilottraining_assistants, 'name' => 'Training Assistant', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_webservices, 'name' => 'Web Services Director', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_webservices_assistants, 'name' => 'Design', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_webservices_assistants, 'name' => 'Development', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_webservices_assistants, 'name' => 'Support', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
            ['parent_id' => $department_webservices_assistants, 'name' => 'Systems Administrator', 'type' => 'P', 'created_at' => $date, 'updated_at' => $date],
        ]);

        $service_community = DB::table('staff_services')->insertGetId(['name' => 'Community (Forum)', 'created_at' => $date, 'updated_at' => $date]);
        $service_core = DB::table('staff_services')->insertGetId(['name' => 'Core', 'created_at' => $date, 'updated_at' => $date]);
        $service_events = DB::table('staff_services')->insertGetId(['name' => 'Events', 'created_at' => $date, 'updated_at' => $date]);
        $service_helpdesk = DB::table('staff_services')->insertGetId(['name' => 'Helpdesk', 'created_at' => $date, 'updated_at' => $date]);
        $service_moodle = DB::table('staff_services')->insertGetId(['name' => 'Moodle', 'created_at' => $date, 'updated_at' => $date]);
        $service_rts = DB::table('staff_services')->insertGetId(['name' => 'RTS System', 'created_at' => $date, 'updated_at' => $date]);
        $service_servers = DB::table('staff_services')->insertGetId(['name' => 'Servers', 'created_at' => $date, 'updated_at' => $date]);
        $service_status = DB::table('staff_services')->insertGetId(['name' => 'Status', 'created_at' => $date, 'updated_at' => $date]);
        $service_site = DB::table('staff_services')->insertGetId(['name' => 'Website', 'created_at' => $date, 'updated_at' => $date]);

        DB::table('staff_attributes')->insert([
            ['service_id' => $service_community, 'name' => 'ACP Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_core, 'name' => 'Admin Access (Backend)', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_events, 'name' => 'Git Repository Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_helpdesk, 'name' => 'SCP Access (Department Specific)', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_helpdesk, 'name' => 'SCP Admin Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_moodle, 'name' => 'Admin Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_rts, 'name' => 'Git Repository Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_rts, 'name' => 'Mentoring Access (RTS Specific)', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_rts, 'name' => 'RTSM/Lead Mentor Access (RTS Specific)', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_rts, 'name' => 'Examiner Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_rts, 'name' => 'Administrator Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_servers, 'name' => 'Privileged Access', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_servers, 'name' => 'Elevated Access (SSH User Account)', 'created_at' => $date, 'updated_at' => $date],
            ['service_id' => $service_site, 'name' => 'ACP Access', 'created_at' => $date, 'updated_at' => $date],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('staff_positions')->truncate();
        DB::table('staff_services')->truncate();
        DB::table('staff_attributes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
