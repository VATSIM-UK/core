<?php

use Illuminate\Database\Schema\Blueprint;
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
        $department_management = DB::table('staff_departments')->insertGetId(['name' => 'Management']);
        $department_community = DB::table('staff_departments')->insertGetId(['parent_id' => $department_management, 'name' => 'Community']);
        $department_marketing = DB::table('staff_departments')->insertGetId(['parent_id' => $department_management, 'name' => 'Marketing']);
        $department_atctraining = DB::table('staff_departments')->insertGetId(['parent_id' => $department_management, 'name' => 'ATC Training']);
        $department_atctraining_instructors = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining, 'name' => 'Instructors']);
        $department_atctraining_rtsms = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining, 'name' => 'RTS Managers']);
        $department_atctraining_rtsms_essex = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Essex']);
        $department_atctraining_rtsms_heathrow = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Heathrow']);
        $department_atctraining_rtsms_london = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'London']);
        $department_atctraining_rtsms_northern = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Northern']);
        $department_atctraining_rtsms_sconi = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'Scotland & Northern Ireland']);
        $department_atctraining_rtsms_serts = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'South East']);
        $department_atctraining_rtsms_swrts = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_rtsms, 'name' => 'South West']);
        $department_atctraining_mentors = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining, 'name' => 'Mentors']);
        $department_atctraining_mentors_essex = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Essex']);
        $department_atctraining_mentors_essex_luton = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_essex, 'name' => 'London Luton (EGGW)']);
        $department_atctraining_mentors_essex_stansted = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_essex, 'name' => 'London Stansted (EGSS)']);
        $department_atctraining_mentors_heathrow = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Heathrow']);
        $department_atctraining_mentors_heathrow_heathrow = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_heathrow, 'name' => 'London Heathrow (EGLL)']);
        $department_atctraining_mentors_london = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'London']);
        $department_atctraining_mentors_london_london = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_london, 'name' => 'London Area Control']);
        $department_atctraining_mentors_northern = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Northern']);
        $department_atctraining_mentors_northern_birmingham = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'Birmingham (EGBB)']);
        $department_atctraining_mentors_northern_eastmids = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'East Midlands (EGNX)']);
        $department_atctraining_mentors_northern_liverpool = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'Liverpool (EGGP)']);
        $department_atctraining_mentors_northern_manchester = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_northern, 'name' => 'Manchester (EGCC)']);
        $department_atctraining_mentors_sconi = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'Scotland & Northern Ireland']);
        $department_atctraining_mentors_sconi_edinburgh = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Edinburgh (EGPH)']);
        $department_atctraining_mentors_sconi_glasgow = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Glasgow (EGPF)']);
        $department_atctraining_mentors_sconi_scottish = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Scottish Area Control']);
        $department_atctraining_mentors_sconi_shanwick = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_sconi, 'name' => 'Shanwick Oceanic Control']);
        $department_atctraining_mentors_serts = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'South East']);
        $department_atctraining_mentors_serts_city = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_serts, 'name' => 'London City (EGLC)']);
        $department_atctraining_mentors_serts_gatwick = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_serts, 'name' => 'London Gatwick (EGKK)']);
        $department_atctraining_mentors_swrts = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors, 'name' => 'South West']);
        $department_atctraining_mentors_swrts_bristol = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_swrts, 'name' => 'Bristol (EGGD)']);
        $department_atctraining_mentors_swrts_jersey = DB::table('staff_departments')->insertGetId(['parent_id' => $department_atctraining_mentors_swrts, 'name' => 'Jersey (EGJJ)']);
        $department_pilottraining = DB::table('staff_departments')->insertGetId(['parent_id' => $department_management, 'name' => 'Pilot Training']);
        $department_pilottraining_instructors = DB::table('staff_departments')->insertGetId(['parent_id' => $department_pilottraining, 'name' => 'Instructors']);
        $department_pilottraining_assistants = DB::table('staff_departments')->insertGetId(['parent_id' => $department_pilottraining, 'name' => 'Assistants']);
        $department_webservices = DB::table('staff_departments')->insertGetId(['parent_id' => $department_management, 'name' => 'Web Services']);
        $department_webservices_assistants = DB::table('staff_departments')->insertGetId(['parent_id' => $department_webservices, 'name' => 'Assistants']);

        DB::table('staff_positions')->insert(array(
            ['department_id' => $department_management, 'name' => 'Division Director'],
            ['department_id' => $department_management, 'name' => 'Deputy Division Director'],
            ['department_id' => $department_community, 'name' => 'Community Director'],
            ['department_id' => $department_marketing, 'name' => 'Marketing Director'],
            ['department_id' => $department_atctraining, 'name' => 'ATC Training Director'],
            ['department_id' => $department_atctraining, 'name' => 'Deputy ATC Training Director'],
            ['department_id' => $department_atctraining_instructors, 'name' => 'Senior Instructor'],
            ['department_id' => $department_atctraining_instructors, 'name' => 'Instructor (Area)'],
            ['department_id' => $department_atctraining_instructors, 'name' => 'Instructor (Approach & Aerodrome)'],
            ['department_id' => $department_atctraining_mentors, 'name' => 'Head Mentor (Area)'],
            ['department_id' => $department_atctraining_mentors, 'name' => 'Head Mentor (Approach & Aerodrome)'],
            ['department_id' => $department_atctraining_mentors_essex_luton, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_essex_stansted, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_heathrow_heathrow, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_london_london, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_northern_birmingham, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_northern_eastmids, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_northern_liverpool, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_northern_manchester, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_sconi_edinburgh, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_sconi_glasgow, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_sconi_scottish, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_sconi_shanwick, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_serts_city, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_serts_gatwick, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_swrts_bristol, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_mentors_swrts_jersey, 'name' => 'Lead Mentor'],
            ['department_id' => $department_atctraining_rtsms_essex, 'name' => 'RTS Manager'],
            ['department_id' => $department_atctraining_rtsms_heathrow, 'name' => 'RTS Manager'],
            ['department_id' => $department_atctraining_rtsms_london, 'name' => 'RTS Manager'],
            ['department_id' => $department_atctraining_rtsms_northern, 'name' => 'RTS Manager'],
            ['department_id' => $department_atctraining_rtsms_sconi, 'name' => 'RTS Manager'],
            ['department_id' => $department_atctraining_rtsms_serts, 'name' => 'RTS Manager'],
            ['department_id' => $department_atctraining_rtsms_swrts, 'name' => 'RTS Manager'],
            ['department_id' => $department_pilottraining, 'name' => 'Pilot Training Director'],
            ['department_id' => $department_pilottraining_instructors, 'name' => 'Flight Training (P1)'],
            ['department_id' => $department_pilottraining_instructors, 'name' => 'Flight Training (P2)'],
            ['department_id' => $department_pilottraining_instructors, 'name' => 'Flight Training (P3)'],
            ['department_id' => $department_pilottraining_assistants, 'name' => 'Training Assistant'],
            ['department_id' => $department_webservices, 'name' => 'Web Services Director'],
            ['department_id' => $department_webservices_assistants, 'name' => 'Design'],
            ['department_id' => $department_webservices_assistants, 'name' => 'Development'],
            ['department_id' => $department_webservices_assistants, 'name' => 'Support'],
            ['department_id' => $department_webservices_assistants, 'name' => 'Systems Administrator'],
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('staff_positions')->truncate();
        DB::table('staff_departments')->truncate();
    }
}
