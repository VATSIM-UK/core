/**
 * Change the download type when adding a new download.
 */
function downloadsAddChangeType(){
    if($("#type").val() == 'local'){
        $("#externalDownload").hide();
        $("#localDownload").show();

        // Clear the values of all fields
        $("#websiteURI").val("http://");
        $("#downloadURI").val("http://");
    } else if($("#type").val() == 'external') {
        $("#localDownload").hide();
        $("#externalDownload").show();

        // Clear the values of all fields
        $("#description").val("");
        $("#allowOldVersions").val("NO");
        $("#enableChangelog").val("NO");
    } else {
        $("#externalDownload").hide();
        $("#localDownload").hide();

        // Clear the values of all fields
        $("#description").val("");
        $("#allowOldVersions").val("NO");
        $("#enableChangelog").val("NO");
        $("#websiteURI").val("http://");
        $("#downloadURI").val("http://");
    }
}