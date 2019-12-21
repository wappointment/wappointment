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
                $group .= '<p>' . nl2br($message['message']) . '</p>';
            }
            if (!empty($group)) {
                $finalMessages .= sprintf('<div class="d-flex align-items-center notice notice-%1$s">
                <div class="notice-' . WAPPOINTMENT_SLUG
                    . '"><span class="dashicons-before dashicons-wappointment text-primary"></span></div>
                <div class="ml-2 notice-text">%2$s</div>
                <div class="clear"></div>
                </div>', $type, $group);
            }
        }
        echo $finalMessages;
    }

    protected static function get()
    {

        return self::$messages;
    }

    protected static function record($type, $message, $pages = [])
    {
        self::$messages[$type][] = ['message' => $message, 'pages' => $pages];
    }

    protected static function setupMessage($message, $details)
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
