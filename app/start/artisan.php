<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new MembersCertImport());
Artisan::add(new MembersCertUpdate());
Artisan::add(new RebuildModelDependencies());
Artisan::add(new PostmasterParse());
Artisan::add(new PostmasterDispatch());
Artisan::add(new KohanaUpgrade());
Artisan::add(new RtsSync());
