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