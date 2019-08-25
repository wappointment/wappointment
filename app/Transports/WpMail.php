<?php

namespace Wappointment\Transports;

use WappoSwift_Mime_SimpleMessage;

class WpMail extends Transport
{
    private $configSave = [];

    public function __construct($config)
    {
        $this->configSave = $config;
    }
    public function wp_mail_from_name()
    {
        return $this->configSave['from_name'];
    }
    public function wp_mail_from()
    {
        return $this->configSave['from_address'];
    }
    public function wp_mail_content_type()
    {
        return 'text/html';
    }
    public function setWpSettings()
    {
        add_filter('wp_mail_from_name', [$this, 'wp_mail_from_name']);
        add_filter('wp_mail_from', [$this, 'wp_mail_from']);
        add_filter('wp_mail_content_type', [$this, 'wp_mail_content_type']);
    }

    public function unsetWpSettings()
    {
        remove_filter('wp_mail_from_name', [$this, 'wp_mail_from_name']);
        remove_filter('wp_mail_from', [$this, 'wp_mail_from']);
        remove_filter('wp_mail_content_type', [$this, 'wp_mail_content_type']);
    }
    public function send(WappoSwift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        
        $to = $this->getTo($message);

        $message->setBcc([]);
        $this->setWpSettings();
        wp_mail($to, $message->getSubject(), $message->getBody(), $message->getHeaders());
        $this->unsetWpSettings();
        return true;
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
