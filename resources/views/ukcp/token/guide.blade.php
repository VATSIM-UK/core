@extends ('layout')

@section('content')
    <div class="row equal">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-chart-line"></i> &thinsp; UK Controller Plugin
                    Setup
                </div>
                <div class="panel-body">
                    <div class="text-center">
                        The UK Controller Plugin (UKCP) is a EuroScope 3.2 plugin that is designed to assist controllers
                        in the UK.<br/>
                        A full feature list can be found by <a
                                href="https://github.com/VATSIM-UK/uk-controller-plugin/blob/main/docs/UserGuide/UserGuide.md"
                                target="_blank">clicking
                            here</a>.<br/>
                        <br/>
                        The code for the plugin (and it's API) are open source and can be found within VATSIM UK's
                        GitHub organisation by <a href="https://github.com/VATSIM-UK" target="_blank">clicking here</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="fa fa-arrow-down"></span>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-download"></i> &thinsp; Step One - Download
                    Plugin
                </div>
                <div class="panel-body">
                    EuroScope allows you to load plugins from one simple file.<br/>
                    Click below to download the latest version of the UK Controller Plugin.<br/>
                    <br/>
                    <a href="https://github.com/VATSIM-UK/uk-controller-plugin/releases/"
                       target="_blank">
                        <button class="btn btn-primary center-block">Download UK Controller Plugin</button>
                    </a>

                    <br/>

                    The plugin relies on <a
                            href="https://support.microsoft.com/en-gb/help/2977003/the-latest-supported-visual-c-downloads"
                            target="_blank">a
                        package from Microsoft</a>. You can download that below too.
                    <br/>
                    <a href="https://aka.ms/vs/17/release/VC_redist.x86.exe"
                       target="_blank">
                        <br/>
                        <button class="btn btn-primary center-block">Download Visual C++ Redistributable Package
                        </button>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-cog"></i> &thinsp; Step Two - Install and Start EuroScope
                    Plugin
                </div>
                <div class="panel-body">
                    <ol>
                        <li>Move the UKControllerPlugin.dll file (downloaded in Step One) to /Data/Plugins within
                            your UK Sector File folder.
                        </li>
                        <li>Open EuroScope.</li>
                        <li>Click Other Set, then Plugins.</li>
                        <li>Click "Load" on the dialog that opens.</li>
                        <li>Select the UKControllerPlugin.dll file from the /Data/Plugins directory.</li>
                        <li>If this is your first time setting up the plugin, see the Step Three for configuring your personal credentials.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="fa fa-arrow-down"></span>
            </div>
        </div>
    </div>


    <div class="row equal">
        <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="fa fa-download"></i> &thinsp; Step Three - First Time Credential Setup
                </div>
                <div class="panel-body">
                    When loading the plugin into EuroScope for the first time, you will be asked to login to the VATSIM UK website.
                    Doing this will allow the plugin to receive your personal plugin credentials, which are required to authenticate
                    actions such as assigning squawk codes.<br/>
                    <br/>

                    If you would like to generate a fresh key, you may do so using the option in the "OP" menu on the bottom right of the
                    EuroScope radar screen, once the plugin has loaded.<br/><br/>
                    
                    <p>The UK Controller Plugin should now be fully installed!</p>
                    
                    <p>Feel free to check out the settings by clicking the toggle at the bottom right of your radar
                        screen. You may also wish to browse the <a
                                href="https://github.com/VATSIM-UK/uk-controller-plugin/blob/develop/docs/README.md">full
                            feature list</a>.</p>

                    <p>Having trouble or got questions? Ask on <a href="{{ route('mship.manage.dashboard') }}">our Discord</a>!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
    </div>
@stop
