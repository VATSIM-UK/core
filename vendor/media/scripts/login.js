/* 
 * Script: login.js
 * Author: Kieran Hardern
 * Description:
 *		Ajax Login and logout functionality
 */

$(function(){
	
	//login button is clicked
	$("#loginForm").submit(function(){
		//call the login page and post the login data
		$('#loginReturn').load('/ajax/login/', 
			{
				'cid': $("#loginCID").val(),
				'pass': $("#loginPass").val()
			}
		);
		return false;
	});
	
	//logout button is clicked
	$("#logoutYes").click(function(){
		//call the logout page
		$('#loginReturn').load('/ajax/login/logout/');
	});
	
	//the logout cancel button is clicked
	$("#logoutNo").click(function(){
		$("#loginBox").dialog('close');
	});
	
});