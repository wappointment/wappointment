<?php

namespace Wappointment\Transports;

use WappoSwift_Mime_SimpleMessage;
use WappoSwift_MimePart;
use WappoSwift_Attachment;
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
     * Get the HTTP payload for sending the Mailgun message.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @param  string  $to
     * @return array
     */
    protected function payload(WappoSwift_Mime_SimpleMessage $message, $to)
    {

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

        $attachments = $this->getAttachments($message);
        if (count($attachments) > 0) {
            $data['attachments'] = $attachments;
        }

        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];
    }

    private function getAttachments(WappoSwift_Mime_SimpleMessage $message)
    {
        $attachments = [];
        foreach ($message->getChildren() as $attachment) {
            if (!$attachment instanceof WappoSwift_Attachment) {
                continue;
            }
            $attachments[] = [
                'content'     => base64_encode($attachment->getBody()),
                'filename'    => $attachment->getFilename(),
                'type'        => $attachment->getContentType(),
                'disposition' => $attachment->getDisposition(),
                'content_id'  => $attachment->getId(),
            ];
        }
        return $this->attachments = $attachments;
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
