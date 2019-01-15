@extends ('layout')

@section('content')
    <div class="row equal">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-signal"></i> &thinsp; UK Controller Plugin
                    Setup
                </div>
                <div class="panel-body">
                    <div class="text-center">
                        The UK Controller Plugin (UKCP) is a EuroScope 3.2 plugin that is designed to assist controllers
                        in the UK.<br/>
                        A full feature list can be found by <a
                                href="https://github.com/VATSIM-UK/uk-controller-plugin/blob/develop/docs/README.md"
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
                <span class="glyphicon glyphicon-arrow-down"></span>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-download"></i> &thinsp; Step One - Download
                    Plugin
                </div>
                <div class="panel-body">
                    EuroScope allows you to load plugins from one simple file.<br/>
                    Click below to download the latest version of the UK Controller Plugin.<br/>
                    <br/>
                    <a href="https://community.vatsim.uk/files/downloads/file/215-uk-controller-plugin/"
                       target="_blank">
                        <button class="btn btn-primary center-block">Download UK Controller Plugin</button>
                    </a>

                    <br/>

                    The plugin relies on <a
                            href="https://support.microsoft.com/en-gb/help/2977003/the-latest-supported-visual-c-downloads"
                            target="_blank">a
                        package from Microsoft</a>. You can download that below too.
                    <br/>
                    <a href="https://aka.ms/vs/15/release/VC_redist.x86.exe"
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
                <div class="panel-heading"><i class="glyphicon glyphicon-download"></i> &thinsp; Step Two - Download Key
                </div>
                <div class="panel-body">
                    When loading the plugin into EuroScope for the first time, you will be asked to provide a key so
                    that the plugin knows who is connecting.<br/>
                    <br/>
                    Please click the button below to download your key. <br/><br/><b>You should never share your key
                        with
                        anyone
                        else.</b><br/>
                    <br/>

                    <a href="{{ route('ukcp.token.download', $newToken) }}">
                        <button class="btn btn-primary center-block">Download My Key</button>
                    </a>

                </div>
            </div>
        </div>
    </div>

    <div class="row equal">
        <div class="col-md-12">
            <div class="text-center" style="padding-bottom: 20px;">
                <span class="glyphicon glyphicon-arrow-down"></span>
            </div>
        </div>
    </div>


    <div class="row equal">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-cog"></i> &thinsp; Step Three - Install
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
                        <li>A warning will appear, press OK.</li>
                        <li>The plugin will then ask you to find the key file that you downloaded in Step Two. Find it
                            and click Open.
                        </li>
                        <li>Select the UK Controller Plugin from the list of plugins and move "Standard ES Radar Screen"
                            from forbidden to allowed in the section below.
                        </li>
                        <li>Click close - the UK Controller Plugin has now been installed!</li>
                    </ol>

                    <p>Feel free to check out the settings by clicking the toggle at the bottom right of your radar
                        screen. You may also wish to browse the <a
                                href="https://github.com/VATSIM-UK/uk-controller-plugin/blob/develop/docs/README.md">full
                            feature list</a>.</p>

                    <p>Having trouble or got questions? Ask <a href="{{ route('slack.new') }}">on Slack</a> or <a
                                href="https://community.vatsim.uk">our Forum</a>!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
    </div>
@stop
