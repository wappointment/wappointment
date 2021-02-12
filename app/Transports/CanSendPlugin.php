<?php

namespace Wappointment\Transports;

trait CanSendPlugin
{
    public function sendPluginVersion($to, $message)
    {
        add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
        wp_mail($to, $message->getSubject(), $message->getBody());
        remove_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
    }
}
