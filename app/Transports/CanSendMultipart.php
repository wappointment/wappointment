<?php

namespace Wappointment\Transports;

trait CanSendMultipart
{
    public function sendMultiPartVersion($to, $message)
    {
        add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
        $this->wpMail($to, $message->getSubject(), $this->multipartBody($message), $message->getHeaders(), $this->getAttachments($message));
        remove_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
    }

    public function getAttachments($message)
    {
        $attachments = [];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof \WappoSwift_Attachment) {
                $attachments[] = [
                    'body' => $child->getBody(),
                    'type' => 'text/calendar',
                    'name' => 'appointments.ics',
                ];
            }
        }

        return $attachments;
    }

    public function multipartBody($message)
    {
        return [
            'text/html' => $message->getBody(),
            'text/plain' => $this->getTextVersion($message)
        ];
    }
}
