<?php

namespace Wappointment\Transports;

use WappoSwift_Mime_SimpleMessage;
use Wappointment\Services\Status;

class WpMail extends Transport
{
    use WpMailPatched, CanSendPlainText, CanSendMultipart, CanSendPlugin;
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

    public function setWpSettings()
    {
        add_filter('wpMailFromName', [$this, 'wpMailFromName']);
        add_filter('wpMailFrom', [$this, 'wpMailFrom']);
    }

    public function send(WappoSwift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $to = $this->getTo($message);

        $message->setBcc([]);
        $this->setWpSettings();

        $this->sendTheRightWay($to, $message);

        $this->unsetWpSettings();
        return true;
    }

    public function sendTheRightWay($to, $message)
    {
        //if wpforms or other smtp plugins is installed
        if (Status::hasSmtpPlugin() || Status::hasEmailConflict()) {
            return $this->sendPluginVersion($to, $message);
        } else {
            return empty($this->configSave['wpmail_html']) ?
                $this->sendTextVersion($to, $message) : $this->sendMultiPartVersion($to, $message);
        }
    }

    public function unsetWpSettings()
    {
        remove_filter('wpMailFromName', [$this, 'wpMailFromName']);
        remove_filter('wpMailFrom', [$this, 'wpMailFrom']);
    }

    public function setHtmlContentType()
    {
        return Status::hasSmtpPlugin() || Status::hasEmailConflict() ? 'text/html' : 'multipart/alternative';
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
