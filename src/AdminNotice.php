<?php

namespace Laraish\AdminNotice;

class AdminNotice
{
    public const TRANSIENT_KEY = 'laraish_notices';
    public const DISMISS_ACTION = 'laraish_dismiss';

    /**
     * The accumulated notices.
     *
     * @var array
     */
    protected static $notices = [];

    public static function init()
    {
        static::$notices = get_transient(static::TRANSIENT_KEY) ?: [];

        add_action('admin_notices', function () {
            static::outputNotices();
        });

        add_action('wp_ajax_'.static::DISMISS_ACTION, function () {
            static::ajaxDismiss();
        });


        $absolutePath = str_replace('\\', '/', ABSPATH);
        $assetsDir = str_replace('\\', '/', dirname(__DIR__).'/assets');
        $assetsUrl = home_url(str_replace($absolutePath, '', $assetsDir));

        add_action('admin_enqueue_scripts', function ($hook) use ($assetsUrl) {
            wp_enqueue_script(
                'laraish-dismiss-notice',
                $assetsUrl.'/dismiss-notice.js',
                ['jquery'],
                null,
                true
            );
        });
    }

    /**
     * Process an AJAX request to dismiss this notice.
     *
     * @internal
     */
    protected static function ajaxDismiss()
    {
        check_ajax_referer(static::DISMISS_ACTION);

        if (!is_user_logged_in()) {
            wp_die('Access denied. You need to be logged in to dismiss notices.');
            return;
        }

        $noticeId = filter_input(INPUT_POST, 'notice_id', FILTER_SANITIZE_STRING);

        static::removePersistentNotice($noticeId);

        exit("Notice(${noticeId}) dismissed");
    }


    /**
     * @param  string  $type
     * @param  string  $message
     * @param  bool  $isDismissible
     * @param  null  $id
     */
    protected static function addNotice(string $type, string $message, bool $isDismissible = true, $id = null)
    {
        $noticeToBeAdded = compact('type', 'message', 'isDismissible', 'id');

        // update the notice with the given id if possible
        $updated = false;
        foreach (static::$notices as &$notice) {
            if ($notice['id'] && $notice['id'] === $id) {
                $notice = $noticeToBeAdded;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            static::$notices[] = $noticeToBeAdded;
        }

        set_transient(static::TRANSIENT_KEY, static::$notices, YEAR_IN_SECONDS);
    }

    public static function success($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('success', $message, $isDismissible, $noticeId);
    }

    public static function error($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('error', $message, $isDismissible, $noticeId);
    }

    public static function warning($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('warning', $message, $isDismissible, $noticeId);
    }

    public static function info($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('info', $message, $isDismissible, $noticeId);
    }

    /**
     * Out put all the messages added.
     */
    protected static function outputNotices()
    {
        foreach (static::$notices as $notice) {
            $class = 'notice notice-'.$notice['type'].($notice['isDismissible'] ? ' is-dismissible' : '');
            $message = $notice['message'];
            $noticeId = $notice['id'];
            $dataNoticeId = $noticeId ? "data-notice-id=\"{$noticeId}\"" : '';
            $dismissNonce = $noticeId ? wp_create_nonce(static::DISMISS_ACTION) : null;
            $dataDismissNonce = $noticeId ? "data-dismiss-nonce=\"{$dismissNonce}\"" : '';
            echo "<div class=\"{$class}\" {$dataNoticeId} {$dataDismissNonce}><p>{$message}</p></div>";
        }

        static::removeOneTimeNotice();
    }

    protected static function removeOneTimeNotice()
    {
        $notices = array_filter(static::$notices, function ($notice) {
            return $notice['id'];
        });

        set_transient(static::TRANSIENT_KEY, $notices, YEAR_IN_SECONDS);
    }

    protected static function removePersistentNotice(string $noticeId)
    {
        $notices = [];

        foreach (static::$notices as $notice) {
            if (isset($notice['id']) && $notice['id'] === $noticeId) {
                continue;
            }

            $notices[] = $notice;
        }

        set_transient(static::TRANSIENT_KEY, $notices, YEAR_IN_SECONDS);
    }

    public static function clearAll()
    {
        return delete_transient(static::TRANSIENT_KEY);
    }
}