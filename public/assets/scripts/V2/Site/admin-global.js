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