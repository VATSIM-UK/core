function switchUcpContainer(container_id, action){
	if(action == "focus"){
		$(container_id).css('background', '#ddeaed');
	} else if(action == "blur"){
		$(container_id).css('background', 'none');
	}
}
$(document).ready(function(){
	$('#vat_cid').click(function(){
		if($('#vat_cid').attr('background', 'none')){
			$('#var_cid').css('background', '#ddeaed');
			$('#var_cid').focus();
		} else {
			$('#var_cid').css('background', 'none');
		}
	});
	$('#vat_pass').click(function(){
		if($('#vat_pass').attr('background', 'none')){
			$('#var_pass').css('background', '#ddeaed');
			$('#var_pass').focus();
		} else {
			$('#var_pass').css('background', 'none');
		}
	});
});