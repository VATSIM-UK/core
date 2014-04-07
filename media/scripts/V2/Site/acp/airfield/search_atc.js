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