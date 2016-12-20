<?php

namespace App\Modules\Ais\Console\Commands;

use League\Csv\Reader;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ImportAirports extends \App\Console\Commands\Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ais:import-airports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and Import airfield data from OurAirports.';

    private $totalAirportsInserted = 0;
    private $ourAirportsData       = null;

    /**
     * Executes all necessary console commands.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Downloading OurAirports data file.', 'v');

        $this->loadFile();

        $this->info('Downloaded OurAirports Data', 'v');

        $this->info('Inserting all OurAirports Data.', 'v');

        $this->insertData();

        $this->info('Inserted all '.$this->totalAirportsInserted.' OurAirports Data.', 'v');

        $this->sendSlackSuccess('Completed Successfully', [
            'Airports Inserted' => $this->totalAirportsInserted,
        ]);
    }

    private function loadFile()
    {
        $fileNameRelative = 'ourairports'.DIRECTORY_SEPARATOR.'airports.csv';
        $csvTarget        = storage_path('app'.DIRECTORY_SEPARATOR.$fileNameRelative);

        $this->info("\tRelative file: ".$fileNameRelative, 'vv');
        $this->info("\tAbsolute file: ".$csvTarget, 'vv');

        $contents = file_get_contents('http://ourairports.com/data/airports.csv');
        \Storage::put($fileNameRelative, $contents);
        $this->info("\tData stored in local CSV.", 'vv');

        $this->info("\tSetting headings and filtering non ICAO airports.", 'vv');
        $this->ourAirportsData = Reader::createFromPath($csvTarget)
                                       ->addFilter(function ($row) {
                                           return strlen($row[1]) == 4;
                                       })->fetchAssoc(0);
        $this->info("\tDone.", 'vv');
    }

    private function insertData()
    {
        $insertData = collect();

        $this->info("\tLooping through all available airport data.", 'vv');
        foreach ($this->ourAirportsData as $row) {
            $this->info("\t\tAdding ".$row['ident'].' '.$row['name'], 'vvv');

            $insertData->push([
                'icao'      => $row['ident'],
                'iata'      => ($row['iata_code'] == '' ? null : $row['iata_code']),
                'name'      => $row['name'],
                'latitude'  => $row['latitude_deg'],
                'longitude' => $row['longitude_deg'],
                'elevation' => $row['elevation_ft'],
                'continent' => $row['continent'],
                'country'   => $row['iso_country'],
            ]);

            $this->totalAirportsInserted++;
        }

        $this->info("\tInserting into ais_airport.", 'vv');
        $insertData->chunk(1000)->each(function ($chunk) {
            $this->info("\t\tInserting chunk.", 'vvv');
            $r = \DB::table('ais_airport')->insert($chunk->toArray());
            $this->info("\t\tInsert completed: ".$r, 'vvv');
        });
        $this->info("\tInserts completed.", 'vv');
    }
}
