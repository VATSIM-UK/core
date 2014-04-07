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