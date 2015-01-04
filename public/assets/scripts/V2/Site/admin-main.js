/**
 * The javascript functions within are specific to the administration area of the website.
 *
 * Licensed for use by VATSIM United Kingdom Division.  The developer should be consulted
 * before any modifications or changes are made to this, or any associated, script
 * (unless otherwise discussed with the developer).
 *
 * The developer reserves the right to withdraw the script from use, at any time without
 * prior notice if it is thought that any part of this copyright notice is being breached.
 *
 * This copyright must remain intact at all times.
 *
 * @author Anthony Lawrence <anthony@vatsim-uk.co.uk>
 * @copyright Copyright (c) 2009, Anthony Lawrence
 * @version 1.0
 * @package Website
 */

/**
 * Expand all menu sections.
 * 
 * @return void
 * @since 1.0
 */
function menuExpandAll(){
    $('.menu-content').show('normal');
}

/**
 * Retract all menu sections.
 * 
 * @return void
 * @since 1.0
 */
function menuRetractAll(){
    $('.menu-content').hide('fast');
}

/**
 * Open the specified menu section and close any open menu sections.
 * 
 * @param string section The section to open.
 * @return void
 * @since 1.0
 */
function menuOpen(section){
    $('#'+section).show('slow');
    $('.menu-content:visible:not(#'+section+')').hide('fast');
}

/**
 * Load the menu (retract all menu sections) and then open the default section.
 * 
 * @param string section The section to open as default.
 * @return void
 * @since 1.0
 */
function menuLoad(section){
    menuRetractAll();
    menuOpen(section);
}

/**
 * Open the specified menu link in the main frame.
 * 
 * @param string uri The URI to open
 * @return void
 * @since 1.0
 */
function menuOpenItem(uri){
    parent.$("#main_frame").attr('src', uri);
}

/**
 * Set up jQuery elements.
 * 
 * @return void
 * @since 1.0
 */
$(function(){
    $("#dialogBox").dialog({
        autoOpen: false,
        bgiframe: true,
        buttons: {
            Ok: function(){
                $(this).dialog('close');
            }
        },
        draggable: false,
        modal: true,
        resizable: false,
        width: 500
    });
});

/**
 * Makes an ajax call using jQuery and then processes the result accordingly.
 * 
 * @param url The URL to call.
 * @param data The data to pass to the URL
 * @param title The title for the dialog box.
 * @param buttons The buttons to display.  If 'null', only 'OK' will be used.
 * @param wailMsg The message to display whilst waiting for the result.
 * 					If 'null', no message will be displayed.
 * @param callType Either 'POST' or 'GET' (Default 'POST').
 * @return boolean True of the result of 'success' is returned.
 * @since 1.0
 */
function makeAjaxCall(url, data, title, buttons, waitMsg, callType){
    /**
	 * Default optional variables.
	 */
    if(buttons == null){
        buttons = {
            Ok: function(){
                $(this).dialog('close');
            }
        };
}
	
if(callType == null){
    callType = 'POST';
}
	
/**
	 * Default the return value.
	 */
var retVal = false;
	
/**
	 * Disable the user submitting the form again.
	 */
$("#submit").attr("disabled", "disabled");
$("#dialogBox").bind('dialogclose', function() {
    $("#submit").removeAttr("disabled");
});
	
/**
	 * Make the ajax call.
	 */
$.ajax({
    type: callType,
    url: url,
    data: data,
    dataType: "json",
    beforeSend: function(XMLHttpRequest){
        /**
			 * If waitMsg is not null, display the wait message.
			 */
        if(waitMsg != null){
            $('#dialogBox').dialog({
                dialogClass: 'msg_information'
            });
            $('#dialogBox').dialog('option', 'buttons', { });
				
            $('#dialogBox').dialog('option', 'title', 'Please Wait...');
            $("#dialogBoxText").html("<div class='msg_information'><p>"+waitMsg+"</p></div>");
				
            $("#dialogBox").dialog('open');
        }
    },
    success: function(data){
        /**
			 * If the result is successful, set the return value to true.
			 * Else, change the buttons!
			 */
        if(data['result'] == 'success'){
            retVal = true;
        } else {
            /**
				 * Change the buttons to just 'OK';
				 */
            $('#dialogBox').dialog('option', 'buttons', {
                Ok: function(){
                    $(this).dialog('close');
                }
            });
        }
			
        /**
			 * IF a result message has been returned, display the message.
			 */
        if(data['result_message'] != undefined){
            $('#dialogBox').dialog({
                dialogClass: 'msg_'+data['result']
            });
            $('#dialogBox').dialog('option', 'buttons', buttons);
            $('#dialogBox').dialog('option', 'title', title+' Result');
            $("#dialogBoxText").html("<div class='msg_"+data['result']+"'>" +
                "<p>"+data['result_message']+"</p>" +
                "</div>");
            $("#dialogBox").dialog('open');
        }
			
        /**
			 * If a redirect is set, perform it after several seconds.
			 */
        if(data['redirect'] != undefined){
            setTimeout(function(){
                if(data['target'] != undefined){
                    if(data['target'] == 'parent'){
                        self.parent.location.href = data['redirect'];
                    } else {
                        document.location.href = data['redirect'];
                    }
                } else {
                    document.location.href = data['redirect'];
                }
            }, 2000);
        }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown){
        $('#dialogBox').dialog({
            dialogClass: 'msg_error'
        });
        $('#dialogBox').dialog('option', 'title', 'Fatal Error');
        $('#dialogBox').dialog('option', 'buttons', {
            Ok: function(){
                $(this).dialog('close');
            }
        });
        $("#dialogBoxText").html("<div class='msg_warning'><p>There was a problem communicating" +
            "with the server.  <strong>Please contact support with the" +
            "following information:</strong><br />" +
            "Verbose Report: " + textStatus + "<br />" +
            "Details: " + errorThrown + "</p></div>");
    }
});
	
/**
	 * Return the result.
	 */
return retVal;
}

/**
 * This function fetches the time using an ajax call and then inserts the result
 * into an element with id 'clock'
 * 
 * @return void
 * @since 1.0
 */
function getTime() {
    $("#clock").load(g_ajax+'ajax.gettime.php');
}

/**
 * This function fetches the number of online members using an ajax call and then
 * inserts the result into an element with id 'membersOnline'.
 * 
 * @return void
 * @since 1.0
 */
function getMembersOnline() {
    $.getJSON(g_ajax+'ajax.getmembersonline.php',
        function(data){
            $("#membersOnline").html(data.count);
        });
}

/**
 * This function sets the intervals for the header info.
 */
function headerDetail(){
    getMembersOnline();
    getTime();
	
    setInterval(getMembersOnline, 10000); // 10 second intervals
    setInterval(getTime, 1000); // 1 second intervals
}

/**
 * Fetch the specified log.
 * 
 * @param element The element to output the log entries to.
 * @param timeout The time between refreshing the log.
 * @param key The partial/total key to display log entries for.
 * @param limit The number of log entries to display.
 * @return
 */
function getLogs(element, timeout, key, limit){
    $('#'+element).load(g_ajax+'ajax.admin.getlogs.php', {
        'logKey': key,
        'limit': limit
    });
    setTimeout(function(){
        getLogs(element, timeout, key, limit);
    }, timeout);
}

/**
 * Administration login function verifies the data against the database & CERT
 * then displays the result.
 * If successful, the user is then redirected to the homepage of the admin area.
 * 
 * @return void
 * @since 1.0
 */
function doLogin(){
    /**
	 * Make the ajax call.
	 */
    makeAjaxCall(g_ajax+'ajax.admin.dologin.php',
        'cid='+$('#cid').val()+'&pass='+$('#pass').val(),
        'Login',
        { },
        "Whilst we check your credentials against the membership database " +
        "and the VATSIM Certificate Server.  At times, this can take up " +
        "to a minute or two.<br />The result of the login request will be " +
        "displayed here.</strong>");
}

/**
 * Add new member function.  Stores their CID and email in a "pending users" table ready for the cron script to process at a later stage.
 *
 * @return void
 * @since 1.0
 */
function addNewMember(){
    /**
	 * Make the ajax call.
	 */
    var ajRes = makeAjaxCall(g_ajax+'ajax.admin.doaddmember.php',
        "cid="+$('#cid').val()+"&email="+$('#email').val(),
        'Add New Member',
        { },
        "Whilst we store the information given in the " +
        "database. This process shouldn't take long and " +
        "the result will be presented here.");
	
    /**
	 * If the ajax call was successful (in both calling and the action)
	 * clear the form.
	 */
    if(ajRes == true){
        $('#cid').val(' ');
        $('#email').val(' ');
    }
}

/**
 * Setup the view download page, menu/tabs.
 */
function downloadsViewSetupTabs(){
    $tabsDownload = $('#download').tabs();
}

/**
 * Change the download type when adding a new download.
 */
function downloadsAddChangeType(dropDown){
    if(dropDown == 'local'){
        $("#external_attribs").hide();
        $("#local_attribs").show();
    } else if(dropDown == 'external') {
        $("#local_attribs").hide();
        $("#external_attribs").show();
    } else {
        $("#external_attribs").hide();
        $("#local_attribs").hide();
    }
}

/**
 * Add download function.
 * 
 * Stores the information about a download in a database before moving the user on to allow them
 * to upload the file.
 */
function downloadsAdd(){
    /**
	 * Make the ajax call.
	 */
    var ajRes = makeAjaxCall(g_ajax+'ajax.admin.doadddownload.php',
        "category_id="+$('#category_id').val()+
        "&name="+$('#name').val()+
        "&description="+$("#description").val()+
        "&author="+$("#author").val()+
        "&type="+$("#type").val()+
        "&website_uri="+$("#website_uri").val()+
        "&download_uri="+$("#download_uri").val()+
        "&auto_version_control="+$('#auto_version_control').attr('checked')+
        "&allow_old_versions="+$('#allow_old_versions').attr('checked')+
        "&enable_changelog="+$('#enable_changelog').attr('checked'),
        'Add New Download',
        { },
        "Whilst we store the information given in the " +
        "database. This process shouldn't take long and " +
        "the result will be presented here.");
	
    /**
	 * If the ajax call was successful (in both calling and the action)
	 * send to the upload page.
	 */
    if(ajRes == true){
        menuOpenItem(g_url+g_admin_rel+'downloads/add_version/');
    }
}

/**
 * Upload download function.
 * 
 * Runs the uploadify add-in for this download uploader.
 */
function downloadsUpload(script, upDir){
    $(document).ready(function() {
        $('#fileInput').uploadify({
            'uploader'  : 'uploadify.swf',
            'script'    : script,
            'cancelImg' : '../images/icons/delete-16.png',
            'auto'      : true,
            'folder'    : upDir
        });
    });
}

/**
 * Setup the edit member page, menu/tabs.
 */
function membersEditSetupTabs(){
    $tabsMember = $('#member').tabs();
}

/**
 * Confirm the security question and release all information.
 */
function membersEditSecurityConf(num){
    $("#security_question_1").hide();
    $("#conf_security_answer").show();
}

function AirfieldEditSetupTabs(selectedTab){
    $('#airfield').tabs();

    if(selectedTab != ""){
        $('#airfield').tabs("select", selectedTab);
    }
}

function airfieldNavaidsChangeType(){
    if($("#type").val() == "ILS"){
        $("#typeExtra").parent().show();
    } else {
        $("#typeExtra").parent().hide();
    }
    $("#typeExtra").val("");
}

function airfieldDeleteDialogSetup(uri){
    $(function() {
        $("#dialog-confirm").dialog({
            autoOpen: false,
            resizable: false,
            height:140,
            modal: true,
            buttons: {
                "Delete airfield": function() {
                    $( this ).dialog( "close" );
                    parent.$("#main_frame").attr('src', uri);
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    });
}

function airfieldDeleteConfirm(uri, airfieldName){
    // Modify the dialog text.
    $("#dialog-delete-text").html("Please confirm you wish to delete "+airfieldName+" airfield and all associated information?");

    // Create the dialog box
    airfieldDeleteDialogSetup(uri);

    // Open the dialog box
    $("#dialog-confirm").dialog("open");
}
function ATCDeleteDialogSetup(uri){
    $(function() {
        $("#dialog-confirm").dialog({
            autoOpen: false,
            resizable: false,
            height:140,
            modal: true,
            buttons: {
                "Delete ATC position": function() {
                    $( this ).dialog( "close" );
                    parent.$("#main_frame").attr('src', uri);
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    });
}

function ATCDeleteConfirm(uri, callsign){
    // Modify the dialog text.
    $("#dialog-delete-text").html("Please confirm you wish to delete position "+callsign+"?");

    // Create the dialog box
    ATCDeleteDialogSetup(uri);

    // Open the dialog box
    $("#dialog-confirm").dialog("open");
}