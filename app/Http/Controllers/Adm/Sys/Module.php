<?php

namespace App\Http\Controllers\Adm\Sys;

use Illuminate\Http\Request;
use App\Http\Controllers\Adm\AdmController;

class Module extends AdmController
{
    public function getIndex(Request $request)
    {
        $modules = \Module::all();

        return $this->viewMake('adm.sys.module.list')->with('modules', $modules);
    }

    public function getEnable(Request $request, $slug)
    {
        if (\Module::isEnabled($slug)) {
            return \Redirect::route('adm.sys.module.list')->withError('You cannot enable an already enabled module.');
        }

        \Module::enable($slug);

        $name = \Module::get($slug.'::name');

        return \Redirect::route('adm.sys.module.list')->withSuccess('The '.$name.' module has been enabled.');
    }

    public function getDisable(Request $request, $slug)
    {
        if (\Module::isDisabled($slug)) {
            return \Redirect::route('adm.sys.module.list')->withError('You cannot disable an already disabled module.');
        }

        \Module::disable($slug);

        $name = \Module::get($slug.'::name');

        return \Redirect::route('adm.sys.module.list')->withSuccess('The '.$name.' module has been disabled.');
    }
}
