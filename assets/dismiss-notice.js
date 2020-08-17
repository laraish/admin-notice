jQuery(function ($) {
    $('.notice[data-dismiss-nonce]').on('click', '.notice-dismiss', function () {
        var $notice = $(this).closest('.notice'),
            nonce = $notice.data('dismiss-nonce'),
            notice_id = $notice.data('notice-id');

        $.post(
            ajaxurl,
            {
                "action": 'laraish_dismiss',
                "_ajax_nonce": nonce,
                "notice_id": notice_id
            }
        );
    });
});
