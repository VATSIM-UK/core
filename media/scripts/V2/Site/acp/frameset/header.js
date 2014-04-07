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