<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\BaseController;

class EuroScopeSectorProvider extends BaseController
{
    public function __invoke()
    {
        return redirect()->to('https://www.vatsim.uk/files/sector/esad/VATUK_Euroscope_files.txt');
    }
}
