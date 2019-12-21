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
