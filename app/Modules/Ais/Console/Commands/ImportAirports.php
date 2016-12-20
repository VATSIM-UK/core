<?php

namespace App\Modules\Ais\Console\Commands;

use App\Models\Mship\Account;
use Carbon\Carbon;
use App\Models\Mship\Qualification;
use App\Modules\NetworkData\Models\Atc;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Modules\NetworkData\Events\NetworkDataParsed;
use App\Modules\NetworkData\Events\NetworkDataDownloaded;
use League\Csv\Reader;

class ImportAirports extends \App\Console\Commands\Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ais:import-airfields';

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

        $this->info("Inserting all OurAirports Data.", "v");

        $this->insertData();

        $this->info("Inserted all ".$this->totalAirportsInserted." OurAirports Data.", "v");

        $this->sendSlackSuccess('Completed Successfully', [
            'Airports Inserted' => $this->totalAirportsInserted,
        ]);
    }

    private function loadFile()
    {
        $fileNameRelative = "ourairports" . DIRECTORY_SEPARATOR . "airports.csv";
        $csvTarget = storage_path("app" . DIRECTORY_SEPARATOR . $fileNameRelative);

        $contents = file_get_contents("http://ourairports.com/data/airports.csv");
        \Storage::put($fileNameRelative, $contents);

        $this->ourAirportsData = Reader::createFromPath($csvTarget)
                                       ->addFilter(function ($row) {
                                           return strlen($row[2]) == 4;
                                       })->fetchAssoc(0);

    }

    private function insertData()
    {
        $insertData = [];

        foreach ($this->ourAirportsData as $row) {
            $this->info("\tAdding ".$row['icao']." ".$row['name'], "vvv");

            $insertData[] = [
                "icao"      => $row['icao'],
                "iata"      => $row['iata_code'],
                "name"      => $row['name'],
                "latitude"  => $row['latitude'],
                "longitude" => $row['longitude'],
                "elevation" => $row['elevation_ft'],
                "continent" => $row['continent'],
                "country"   => $row['iso_country'],
            ];

            $this->totalAirportsInserted++;
        }

        \DB::table("ais_airport")->insert($insertData);
    }
}
