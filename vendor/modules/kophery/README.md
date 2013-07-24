# Kophery - Phery jQuery module for Kohana 3.1(.2)

VERY Simplistic [Kohana 3](https://github.com/kohana/kohana) module implementation of [gahgneh's phery](https://github.com/gahgneh/phery) 0.5.1 beta

Phery allows jQuery methods to be called using php.

Kophery adds nothing to gahgne's lib except for the Phery_Utils class and it's quickinit method. This method automatically registers all 'ph_' prefixed methods as phery callbacks, wich saves some typing...

The following web page link with it's "testmethod" target function...

	<?php echo phery::link_to('A click on this link triggers Controller_PheryDemo::ph_testmethod function', 'testmethod', 
	array('confirm' => 'Are you sure?', 'args' => array('hello' => 'This message is passed as data from the calling ink!', 
	'more' => 'More phery data passed...'))); ?>

thus corresponds to the following 'ph_testmethod' controller function:

	public function ph_testmethod($data) 
	{
		// Phery allows jquery methods to be used from php! :-)
		return phery_response::factory('#target')
		->html(print_r($data['hello'], true))
		->css(array('backgroundColor' => 'yellow'))
		->width('400')
		->height('200')
		->alert(print_r($data['more'], true))
		;	
	}	

The jQuery #target selector directs the output to the "target" div - just standard jQuery behaviour and possibilities:

	<div id="target">This text in "target" div is going to be replaced!</div>


## Usage
Add the Kophery module to your Kohana 3.1(.2) installation. Navigate to http://[yourkohanainstallation]/pherydemo

IMPORTANT: Have a look at [gahgneh's repo](https://github.com/gahgneh/phery) for getting a feeling of all the possibilities! This module only sets the parts in place for running as a Kohana module, nothing more!

## Info
This module includes a media controller for loading of the js files. If using in older Ko3 versions, there might be needed a change in the [media controller](https://github.com/cambiata/kophery/blob/master/classes/controller/media.php) (response->check_cache should be replaced with request->check_cache).


## Credits
All credits to [gahgneh](https://github.com/gahgneh)
and the [Kohana team](http://kohanaframework.org/team) for keeping up the great work!