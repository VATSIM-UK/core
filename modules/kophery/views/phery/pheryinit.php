<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>

<?php $mediabase = Route::url('media'); //echo $mediabase; ?>
<script	src="<?php echo $mediabase ?>/phery/js/phery.js"></script>

<style type="text/css">
a {
	/*text-decoration: underline;*/
	cursor: pointer;
}

#loading {
	position: fixed;
	right: 10px;
	top: 10px;
	padding: 14px;
	display: block;
	z-index: 2;
	font-size: 16px;
	font-weight: bold;
	border: 1px solid #aaa;
	background: #eee;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;	
}

.error {
	background: #f00 !important;
}
</style>

<script>
	$(function() {
		var $loading = $('#loading');
		$loading.fadeOut('fast');
		
		// You can set global events to be triggered, in this case, fadeIn and out the loading div
		$.phery.events.before = function(){
			//alert('before');
			$loading.removeClass('error').fadeIn('fast');			
		}
		
		$.phery.events.complete = function(){
			$loading.fadeOut('fast');
			//alert('complete');
		}
		
		$.phery.events.error = function(){
			$loading.addClass('error');
		}
		
		//$loading.fadeOut(0);
		$.phery.events.after = function(){
			//alert('after');
			//$loading.fadeOut('fast');			
		}
	});

</script>

<div id="loading">Loading...</div>
