<?php

namespace Wappointment\Services;

use WappoSwift_Mailer;
use WappoSwift_Message;

class Mail
{
    private static $transportMethod = false;
    private $config = [];
    private $to = [];
    private $from = [];
    private $subject = '';
    private $body = '';
    private $bodyVersion = 'text/html';
    private $alt = '';
    private $altVersion = 'text/plain';
    public $attachments = [];

    public function __construct($configOverride = false)
    {
        $this->config = ($configOverride === false) ? Settings::get('mail_config') : $configOverride;

        $this->from($this->config['from_address'], $this->config['from_name']);

        $this->setTransportMethod();
        if (self::$transportMethod === false) {
            throw new \WappointmentException('Mail config missing', 199);
        }
    }

    public function send(\Wappointment\Messages\AbstractEmail $email)
    {
        $message = $email->renderMessage();

        $this->body($message['body'])
            ->subject($message['subject'])
            ->alt($message['body_text']); //tags already replace in renderBody

        $this->setAttachments($email);

        return $this->sendTransport();
    }

    protected function setAttachments(\Wappointment\Messages\AbstractEmail $email)
    {
        $this->attachments = $email->attachments;
    }

    public function sendFast($subject, $body, $recipient, $from = false, $version = 'text/plain')
    {
        $this->bodyVersion = $version;
        $this
            ->subject($subject)
            ->body($body, $version)
            ->to($recipient);
        if ($version != 'text/plain') {
            $this->altVersion = 'text/plain';
            $this->alt($this->convertToText($body));
        }
        if (!empty($from)) {
            $this->from($from);
        }
        return $this->sendTransport();
    }

    public function convertToText($htmlBody)
    {
        return \Html2Text\Html2Text::convert($htmlBody);
    }

    public function getFrom()
    {
        return $this->from ? $this->from : [];
    }

    public function getTo()
    {
        return $this->to ? $this->to : [];
    }

    public function from($email, $name = '')
    {
        $this->from = $this->mergeAddresses($this->from, $email, $name);
        return $this;
    }

    public function to($email, $name = '')
    {
        $this->to = $this->mergeAddresses($this->to, $email, $name);
        return $this;
    }

    public function mergeAddresses($addresses, $email, $name = '')
    {
        if (!is_array($email)) {
            $email = [$email => $name];
        }

        return array_merge($addresses, $email);
    }

    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    public function alt($altBody)
    {
        $this->alt = $altBody;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    private function sendTransport()
    {
        $mailer = new WappoSwift_Mailer(self::$transportMethod);
        // Create a message
        $message = (new WappoSwift_Message($this->subject))
            ->setFrom($this->getFrom())
            ->setTo($this->getTo())
            ->setBody($this->body, $this->bodyVersion);
        if (!empty($this->alt)) {
            $message->addPart($this->alt, $this->altVersion);
        }

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $message->attach($attachment);
            }
        }

        // Send the message
        if ($mailer->send($message) !== 0) {
            return true;
        }
        return false;
    }

    private function setTransportMethod()
    {
        if (self::$transportMethod !== false) {
            return;
        }
        self::$transportMethod = $this->getTransportMethod()->setMethod($this->config);
    }

    private function getTransportMethod()
    {

        if ($this->config['method'] == 'mailgun') {
            return (new \Wappointment\Transports\Methods\MailgunEmail());
        } elseif ($this->config['method'] == 'sendgrid') {
            return (new \Wappointment\Transports\Methods\SendgridEmail());
        } elseif ($this->config['method'] == 'smtp') {
            return new \Wappointment\Transports\Methods\SMTPEmail();
        } elseif ($this->isWpMail()) {
            return new \Wappointment\Transports\Methods\WpMailEmail();
        }
    }

    private function isWpMail()
    {
        return $this->config['method'] == 'wpmail';
    }
}
