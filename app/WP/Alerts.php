<?php

namespace Wappointment\WP;


class Alerts
{
    private $type;
    private $message;
    private $pages;
    private static $messages = [];

    public function __construct($type, $message, $pages)
    {
        $this->type = $type;
        $this->message = $message;

        if (empty($pages)) {
            $pages = ['wappointment'];
        }
        $this->pages = $pages;
    }

    public static function error($message, $details = '', $pages = [])
    {
        self::record('error', self::setupMessage($message, $details), $pages);
    }

    public static function warning($message, $details = '', $pages = [])
    {
        self::record('warning', self::setupMessage($message, $details), $pages);
    }

    public static function success($message, $details = '', $pages = [])
    {
        self::record('success', self::setupMessage($message, $details), $pages);
    }

    public static function info($message, $details = '', $pages = [])
    {
        self::record('info', self::setupMessage($message, $details), $pages);
    }

    public static function display()
    {
        $finalMessages = '';

        foreach (self::get() as $type => $messagesGroup) {
            $group = '';
            foreach ($messagesGroup as $message) {
                if (in_array((get_current_screen())->parent_base, $message['pages'])) {
                    $group .= '<p>' . nl2br($message['message']) . '</p>';
                }
            }
            if (!empty($group)) {
                $finalMessages .= sprintf('<div class="notice notice-%1$s">
                <div class="notice-' . WAPPOINTMENT_SLUG . '"><span class="notice-logo">A</span></div>
                <div class="notice-text">%2$s</div>
                <div class="clear"></div>
                </div>', $type, $group);
            }
        }
        echo $finalMessages;
    }

    private static function get()
    {
        return self::$messages;
    }

    private static function record($type, $message, $pages = [])
    {
        self::$messages[$type][] = ['message' => $message, 'pages' => $pages];
    }

    private static function setupMessage($message, $details)
    {
        $message = sprintf(
            '%s ',
            $message
        );

        if (!empty($details)) {
            $message .= sprintf(
                '<div class="details">%s</div>',
                $details
            );
        }
        return $message;
    }
}
