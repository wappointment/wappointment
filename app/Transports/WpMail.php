<?php

namespace Wappointment\Transports;

use WappoSwift_Mime_SimpleMessage;
use WappoSwift_MimePart;
use WappoSwift_Attachment;
use Wappointment\Services\Status;

class WpMail extends Transport
{
    use WpMailPatched;
    private $configSave = [];

    public function __construct($config)
    {
        $this->configSave = $config;
    }

    public function wpMailFromName()
    {
        return $this->configSave['from_name'];
    }

    public function wpMailFrom()
    {
        return $this->configSave['from_address'];
    }

    public function wpMailContentType()
    {
        return 'text/html';
    }

    public function setWpSettings()
    {
        add_filter('wpMailFromName', [$this, 'wpMailFromName']);
        add_filter('wpMailFrom', [$this, 'wpMailFrom']);
        add_filter('wpMailContentType', [$this, 'wpMailContentType']);
    }

    public function unsetWpSettings()
    {
        remove_filter('wpMailFromName', [$this, 'wpMailFromName']);
        remove_filter('wpMailFrom', [$this, 'wpMailFrom']);
        remove_filter('wpMailContentType', [$this, 'wpMailContentType']);
    }

    public function send(WappoSwift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $to = $this->getTo($message);

        $message->setBcc([]);
        $this->setWpSettings();

        //if wpforms is installed
        if (Status::hasSmtpPlugin()) {
            add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
            wp_mail($to, $message->getSubject(), $message->getBody());
            remove_filter('wp_mail_content_type', 'setHtmlContentType');
        } else {
            if (!empty($this->configSave['wpmail_html'])) {
                add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
                $this->wpMail($to, $message->getSubject(), $this->multipartBody($message), $message->getHeaders(), $this->getAttachments($message));
                remove_filter('wp_mail_content_type', 'setHtmlContentType');
            } else {
                wp_mail($to, $message->getSubject(), $message->getBody(), $message->getHeaders());
            }
        }


        $this->unsetWpSettings();
        return true;
    }

    public function getAttachments($message)
    {
        $attachments = [];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof WappoSwift_Attachment) {
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
        $content = ['text/html' => $message->getBody()];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof WappoSwift_MimePart && $child->getContentType() === 'text/plain') {
                $content['text/plain'] = $child->getBody();
            }
        }

        return $content;
    }

    public function setHtmlContentType()
    {
        return 'multipart/alternative';
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @return string
     */
    protected function getTo(WappoSwift_Mime_SimpleMessage $message)
    {
        return \WappointmentLv::collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? $display . " <{$address}>" : $address;
        })->values()->implode(',');
    }

    /**
     * Get all of the contacts for the message.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @return array
     */
    protected function allContacts(WappoSwift_Mime_SimpleMessage $message)
    {
        return array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );
    }
}
