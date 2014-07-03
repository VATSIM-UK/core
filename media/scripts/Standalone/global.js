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

var allowMainMenu = true;
function mainMenuOpen(ovrAllow) {
    if (allowMainMenu || ovrAllow === true) {
        if(ovrAllow === true){
            $("#menuContain").show();
            $("#menuToggle").hide();
            $("#menuToggle").addClass("menuOpen");
            $("#menuToggle").removeClass("menuClosed");
        } else {
            $("#menuToggle").toggle("fade");
            $("#menuContain").toggle("blind", function() {
                $("#menuToggle").addClass("menuOpen");
                $("#menuToggle").removeClass("menuClosed");
            });
        }
    }
}

function mainMenuClose() {
    if (allowMainMenu) {
        $("#menuContain").toggle("blind", function() {
            $("#menuToggle").show();
            $("#menuToggle").addClass("menuClosed");
            $("#menuToggle").removeClass("menuOpen");
        });
    }
}

function toggleStaticMenu() {
    if ($("#staticMenuToggle").is(":checked")) {
        $.cookie("static_menu", "true");
        $(".container-menu").css("position", "static");
        allowMainMenu = false;
        $(".container-menu").prependTo(".container-content > .content");
        $(".container-menu").removeClass("container");
    } else {
        $.removeCookie("static_menu");
        $(".container-menu").css("position", "absolute");
        allowMainMenu = true;
        $(".container-menu").prependTo("#mainContainer");
        $(".container-menu").addClass("container");
    }
}

$(document).ready(function() {
    addTableRowDeleteLinks();

    // Open menu on click/hover
    $("#menuToggle").mouseover(function() {
        if (!$("#menuToggle").hasClass("menuOpen")) {
            $("#menuToggle").addClass("menuOpen");
            $("#menuToggle").removeClass("menuClosed");
            mainMenuOpen();
        }
    });
    // Close when no longer hovering.
    $("#menuContain").hover(null, mainMenuClose);

    // Toggle the static menu on/off
    $("#staticMenuToggle").change(toggleStaticMenu);

    // If a cookie has been set for the status of the static menu, use it!
    if ($.cookie("static_menu") == "true") {
        $("#staticMenuToggle").prop('checked', true);
        toggleStaticMenu();
        mainMenuOpen(true);
    }
});