<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Models\Mship\RoleStaffPosition;
use App\Services\Mship\StaffDepartmentDirectory;
use App\Services\Mship\StaffDirectoryEntry;
use App\Services\Mship\StaffDirectoryService;

class StaffPageController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->addBreadcrumb('Staff', '#');
    }

    public function staff(StaffDirectoryService $staffDirectory)
    {
        $this->setTitle('Staff');
        $this->addBreadcrumb('Staff', route('site.staff'));

        return $this->viewMake('site.staff')
            ->with('atcTrainingDirectory', $staffDirectory->forDepartment(RoleStaffPosition::ATC_TRAINING_DEPARTMENT))
            ->with('technologyDirectory', $staffDirectory->forDepartment(RoleStaffPosition::TECHNOLOGY_DEPARTMENT))
            ->with('managementDirectory', $this->directory([
                ['name' => 'Ben Wright', 'jobTitles' => ['Division Director (VATUK1)']],
                ['name' => 'Nick Marinov', 'jobTitles' => ['Deputy Division Director (VATUK2)']],
            ]))
            ->with('operationsDirectory', $this->directory(
                leads: [
                    ['name' => 'Vacant', 'jobTitles' => ['Operations Director (VATUK9)']],
                ],
                team: [
                    ['name' => 'Ben Walker', 'jobTitles' => ['Deputy Operations Director']],
                    ['name' => 'Vacant', 'jobTitles' => ['Enroute Operations Coordinator']],
                    ['name' => 'Vacant', 'jobTitles' => ['Aerodrome Operations Coordinator']],
                    ['name' => 'Luke Brown', 'jobTitles' => ['Sector File Coordinator']],
                    ['name' => 'Lily Unitt', 'jobTitles' => ['Sector File Coordinator']],
                    ['name' => 'Vacant', 'jobTitles' => ['Events Manager']],
                    ['name' => 'Alex Heirbaut Kitteridge', 'jobTitles' => ['Events Assistant']],
                    ['name' => 'Alice Ford', 'jobTitles' => ['Operations Assistant']],
                    ['name' => 'Chad Byworth', 'jobTitles' => ['Operations Assistant']],
                    ['name' => '1237658', 'jobTitles' => ['Operations Assistant']],
                    ['name' => 'Jack Edwards', 'jobTitles' => ['Operations Assistant']],
                    ['name' => 'Vacant', 'jobTitles' => ['Operations Assistant']],
                    ['name' => 'Oliver Cesarczyk', 'jobTitles' => ['Operations Assistant']],
                ],
            ))
            ->with('pilotTrainingDirectory', $this->directory(
                leads: [
                    ['name' => 'Ben Carpenter', 'jobTitles' => ['Pilot Training Director (VATUK6)']],
                ],
                team: [
                    ['name' => 'Lewis Hammett', 'jobTitles' => ['Pilot Training Manager']],
                    ['name' => 'Andy White', 'jobTitles' => ['Initial Flight Instructor']],
                    ['name' => 'Aiden Bok', 'jobTitles' => ['VFR Flight Instructor']],
                    ['name' => 'Vacant', 'jobTitles' => ['VFR Flight Instructor']],
                    ['name' => 'Firas Bashee', 'jobTitles' => ['IFR Flight Instructor']],
                    ['name' => 'Charlie Innam', 'jobTitles' => ['Advanced Flight Instructor']],
                    ['name' => 'Vacant', 'jobTitles' => ['Advanced Flight Instructor']],
                ],
            ))
            ->with('communityDirectory', $this->directory(
                leads: [
                    ['name' => 'William Shaw', 'jobTitles' => ['Community Director (VATUK3)']],
                ],
                team: [
                    ['name' => 'Vacant', 'jobTitles' => ['Outreach Manager']],
                    ['name' => 'Callum Hicks', 'jobTitles' => ['Outreach Assistant']],
                    ['name' => 'Charles Newman', 'jobTitles' => ['Outreach Assistant']],
                    ['name' => 'Noah Murdoch', 'jobTitles' => ['Outreach Assistant']],
                    ['name' => 'Dylan Parkes', 'jobTitles' => ['VT Assistant']],
                ],
            ));
    }

    /**
     * @param  list<array{name: string, jobTitles: list<string>}>  $leads
     * @param  list<array{name: string, jobTitles: list<string>}>  $team
     */
    private function directory(array $leads, array $team = []): StaffDepartmentDirectory
    {
        $entries = fn (array $items): array => array_map(
            fn (array $item): StaffDirectoryEntry => new StaffDirectoryEntry(
                name: $item['name'],
                jobTitles: $item['jobTitles'],
                sortOrder: 0,
                sortName: $item['name'],
            ),
            $items,
        );

        return new StaffDepartmentDirectory(
            leads: $entries($leads),
            team: $entries($team),
        );
    }
}
