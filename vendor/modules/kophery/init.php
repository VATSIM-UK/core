<?php
Route::set('media', 'media(/<file>)', array('file' => '.+'))
	->defaults(array(
	'controller' 	=> 'media',
	'action'     	=> 'index',
	'file'       	=> NULL,
));
