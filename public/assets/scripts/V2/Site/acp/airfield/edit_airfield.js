function airfieldEditSetupTabs(selectedTab){
    $('#airfield').tabs();

    if(selectedTab != ""){
        $('#airfield').tabs("select", selectedTab);
    }
}

function airfieldNavaidsChangeType(){
    if($("#Navtype").val() == "ILS"){
        $("#typeExtra").parent().show();
	$("#typeExtraHeading").parent().show();
    } else {
        $("#ident").parent().show();
        $("#frequency").parent().show();
        $("#frequencyType").parent().show();
        $("#typeExtra").parent().hide();
	$("#typeExtraHeading").parent().hide();
    }
}

function airfieldSidStarChangeType(){
    if($("#Sidtype").val() == "STAR"){
        $("#initialWaypoint").parent().show();
        $("#runwayID").parent().hide();
        $("#altitude").parent().hide();
    } else {
        $("#initialWaypoint").parent().hide();
        $("#runwayID").parent().show();
        $("#altitude").parent().show();
    }
}

$(document).ready(function() {
    $("#departure").ckeditor( function(){
        /* callback code */
    }, {
        /* Customisation here */
        scayt_autoStartup: true,
        toolbar: "VATUK_Full"
    });
    $("#arrival").ckeditor( function(){
        /* callback code */
    }, {
        /* Customisation here */
        scayt_autoStartup: true,
        toolbar: "VATUK_Full"
    });
    $("#nonStandard").ckeditor( function(){
        /* callback code */
    }, {
        /* Customisation here */
        scayt_autoStartup: true,
        toolbar: "VATUK_Full"
    });
    $("#vfr").ckeditor( function(){
        /* callback code */
    }, {
        /* Customisation here */
        scayt_autoStartup: true,
        toolbar: "VATUK_Full"
    });
    $("#additional").ckeditor( function(){
        /* callback code */
    }, {
        /* Customisation here */
        scayt_autoStartup: true,
        toolbar: "VATUK_Full"
    });
});