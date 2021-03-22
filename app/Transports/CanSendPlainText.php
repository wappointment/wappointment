<?php

namespace Wappointment\Transports;

use WappoSwift_MimePart;

trait CanSendPlainText
{

    public function sendTextVersion($to, $message)
    {
        add_filter('wp_mail_content_type', [$this, 'setTextContentType']);
        wp_mail($to, $message->getSubject(), $this->getTextVersion($message), $message->getHeaders());
        remove_filter('wp_mail_content_type', [$this, 'setTextContentType']);
    }

    public function getTextVersion($message)
    {
        foreach ($message->getChildren() as $child) {
            if ($child instanceof WappoSwift_MimePart && $child->getContentType() === 'text/plain') {
                return $child->getBody();
            }
        }
        return $message->getBody();
    }

    public function setTextContentType()
    {
        return 'text/plain';
    }
}
