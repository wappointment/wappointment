<?php

namespace Wappointment\Transports;

trait CanSendPlugin
{
    public function sendPluginVersion($to, $message)
    {
        add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
        $headers = [];
        $reply_to_string = $message->getReplyString();
        if (!empty($reply_to_string)) {
            $headers[] = 'Reply-To: ' . $reply_to_string;
        }

        wp_mail($to, $message->getSubject(), $message->getBody(), $headers);
        remove_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
    }
}
