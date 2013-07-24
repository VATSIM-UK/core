<?php $mediabase = Route::url('media'); //echo $mediabase; ?>

<!-- jQuery.js and Phery.js are loaded and som styling is set in views/phery/pheryinit.php:  -->
<?php echo View::factory('phery/pheryinit') ?>

<h1>Kophery demo</h1>
<p>A click on the links below triggers Controller_PheryDemo::ph_testmethod and ph_testmethod2 respectively:</p>

<ul>
	<li><?php echo phery::link_to('Click here to trig ph_testmethod', 'testmethod', array('args' => array('somedata' => 'This message is passed as data from the calling link to ph_testmethod!'))); ?></li>
	<li><?php echo phery::link_to('Click here to trig ph_testmethod2, with confirm dialog and alert displayed from target method', 'testmethod2', array('confirm' => 'Are you sure?', 'args' => array('somedata' => 'This message is passed as data from the calling link to ph_testmethod2!', 'moredata' => 'More phery data passed...'))); ?></li>
	<li><?php echo phery::link_to('Click here to trig an undefined target method', 'undefinedmethod'); ?></li>
</ul>
<hr />
<div id="target">This text in "target" div is going to be replaced!</div>



