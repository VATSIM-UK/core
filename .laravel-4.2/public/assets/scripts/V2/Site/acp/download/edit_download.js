function downloadEditSetupTabs(selectedTab){
    $('#download').tabs();

    if(selectedTab != ""){
        $('#download').tabs("select", selectedTab);
    }
}