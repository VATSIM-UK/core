function downloadDeleteDialogSetup(uri){
    $(function() {
        $("#dialog-confirm").dialog({
            autoOpen: false,
            resizable: false,
            height:140,
            modal: true,
            buttons: {
                "Delete download": function() {
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

function downloadDeleteConfirm(uri, downloadName){
    // Modify the dialog text.
    $("#dialog-delete-text").html("Please confirm you wish to delete '"+downloadName+"' and all associated information?");

    // Create the dialog box
    downloadDeleteDialogSetup(uri);

    // Open the dialog box
    $("#dialog-confirm").dialog("open");
}
