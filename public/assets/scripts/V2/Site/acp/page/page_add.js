$(document).ready(function() {
    $("#content").ckeditor( function(){
        /* callback code */
    }, {
        /* Customisation here */
        scayt_autoStartup: true,
        toolbar: "VATUK_Full",
        filebrowserUploadUrl : g_admin_rel+"/page/uploadImage/"
    });
});