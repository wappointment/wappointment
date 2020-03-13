<?php

namespace Wappointment\Transports;

use WappoSwift_Mime_SimpleMessage;
use WappoSwift_MimePart;
use GuzzleHttp\ClientInterface;

class Sendgrid extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The Mailgun API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The Sendgrid username.
     *
     * @var string
     */
    protected $username;

    /**
     * The Mailgun API end-point.
     *
     * @var string
     */
    protected $url = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * Create a new Mailgun transport instance.
     *
     * @param  \GuzzleHttp\ClientInterface  $client
     * @param  string  $key
     * @param  string  $username
     * @return void
     */
    public function __construct(ClientInterface $client, $username, $key)
    {
        $this->key = $key;
        $this->client = $client;
        $this->setUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    public function send(WappoSwift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $to = $this->getTo($message);

        $message->setBcc([]);

        $this->client->post($this->url, $this->payload($message, $to));

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the HTTP payload for sending the Mailgun message.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @param  string  $to
     * @return array
     */
    protected function payload(WappoSwift_Mime_SimpleMessage $message, $to)
    {
        /* {"personalizations": [
            {
                "to": [{"email": "test@example.com"}]}
            ],
            "from": {"email": "test@example.com"},
            "subject": "Sending with SendGrid is Fun",
            "content": [
                {"type": "text/plain", "value": "and easy to do anywhere, even with cURL"}
                ]
            } */
        $data = [
            'personalizations' => $this->getPersonalizations($message),
            'from'             => $this->getFrom($message),
            'subject'          => $message->getSubject(),
        ];

        if ($contents = $this->getContents($message)) {
            $data['content'] = $contents;
        }

        if ($reply_to = $this->getReplyTo($message)) {
            $data['reply_to'] = $reply_to;
        }
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];
    }
    private function getFrom(WappoSwift_Mime_SimpleMessage $message)
    {
        if ($message->getFrom()) {
            foreach ($message->getFrom() as $email => $name) {
                return ['email' => $email, 'name' => $name];
            }
        }
        return [];
    }
    private function getReplyTo(WappoSwift_Mime_SimpleMessage $message)
    {
        if ($message->getReplyTo()) {
            foreach ($message->getReplyTo() as $email => $name) {
                return ['email' => $email, 'name' => $name];
            }
        }
        return null;
    }
    private function getPersonalizations(WappoSwift_Mime_SimpleMessage $message)
    {
        $setter = function (array $addresses) {
            $recipients = [];
            foreach ($addresses as $email => $name) {
                $address = [];
                $address['email'] = $email;
                if ($name) {
                    $address['name'] = $name;
                }
                $recipients[] = $address;
            }
            return $recipients;
        };

        $personalization['to'] = $setter($message->getTo());

        if ($cc = $message->getCc()) {
            $personalization['cc'] = $setter($cc);
        }

        if ($bcc = $message->getBcc()) {
            $personalization['bcc'] = $setter($bcc);
        }

        return [$personalization];
    }
    private function getContents(WappoSwift_Mime_SimpleMessage $message)
    {
        $contentType = $message->getContentType();
        switch ($contentType) {
            case 'text/plain':
                return [
                    [
                        'type'  => 'text/plain',
                        'value' => $message->getBody(),

                    ],
                ];
            case 'text/html':
                return [
                    [
                        'type'  => 'text/html',
                        'value' => $message->getBody(),
                    ],
                ];
        }

        // Following RFC 1341, text/html after text/plain in multipart
        $content = [];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof WappoSwift_MimePart && $child->getContentType() === 'text/plain') {
                $content[] = [
                    'type'  => 'text/plain',
                    'value' => $child->getBody(),
                ];
            }
        }

        if (is_null($message->getBody())) {
            return null;
        }

        $content[] = [
            'type'  => 'text/html',
            'value' => $message->getBody(),
        ];
        return $content;
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

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @param  string  $key
     * @return string
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }

    /**
     * Get the username being used by the transport.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the username being used by the transport.
     *
     * @param  string  $username
     * @return string
     */
    public function setUsername($username)
    {

        return $this->username = $username;
    }
}
