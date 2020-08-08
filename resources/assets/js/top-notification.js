$(window).on('load', function () {
    $('.top_notification_dismiss').click(function() {
        hideNotification($(this).attr('data-top-notification-id'))
    })

    $('.top_notification_dismiss').each(function (index) {
        var notification_id = $(this).attr('data-top-notification-id')

        if(!cookieExists(notification_id)) {
            window.setTimeout(function () {
                showNotification(notification_id)
            }, 3000)
        }
    })
});

function cookieExists(name) {
    return (document.cookie.split('; ').indexOf(name + '=1') !== -1);
}

function setCookie(name, value, expirationInDays) {
    const date = new Date();
    date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + '; ' + 'expires=' + date.toUTCString() +';path=/';
}

function hideNotification(id) {
    var notification = $(".top_notification[data-top-notification-id=" + id + "]")
    notification.slideUp("slow")

    if(cookieExists('vuk_cookie_consent')){
        setCookie(id, 1, notification.attr('data-top-notification-cookie-expiration-days'))
    }
}

function showNotification(id) {
    if(!cookieExists('vuk_cookie_consent')){
        $(".top_notification_dismiss[data-top-notification-id=" + id + "]").text("Close")
    }
    $(".top_notification[data-top-notification-id=" + id + "]").slideDown("slow")
}
