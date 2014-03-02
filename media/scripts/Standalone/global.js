function cloneTableFormRowBelow(tableID) {
    // Add a new row to the table in the design.
    tr = $("#" + tableID + " tr:first").clone();
    tr = tr.find("input").val('').end();
    tr.appendTo("#" + tableID);
    addTableRowDeleteLinks()
}

function addTableRowDeleteLinks() {
    $(".table-delete-link").on("click", function() {
        var tr = $(this).closest('tr');
        tr.css("background-color", "#FF3700");
        tr.fadeOut(400, function() {
            tr.remove();
        });
        return false;
    });
}

$(document).ready(function() {
    addTableRowDeleteLinks();
});