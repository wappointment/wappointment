<?php

namespace Wappointment\Transports;

use WappoSwift_Transport;
use WappoSwift_Events_SendEvent;
use WappoSwift_Mime_SimpleMessage;
use WappoSwift_Events_EventListener;

abstract class Transport implements WappoSwift_Transport
{
    /**
     * The plug-ins registered with the transport.
     *
     * @var array
     */
    public $plugins = [];

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return true;
    }

    /**
     * Register a plug-in with the transport.
     *
     * @param  \WappoSwift_Events_EventListener  $plugin
     * @return void
     */
    public function registerPlugin(WappoSwift_Events_EventListener $plugin)
    {
        array_push($this->plugins, $plugin);
    }

    /**
     * Iterate through registered plugins and execute plugins' methods.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @return void
     */
    protected function beforeSendPerformed(WappoSwift_Mime_SimpleMessage $message)
    {
        $event = new WappoSwift_Events_SendEvent($this, $message);

        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'beforeSendPerformed')) {
                $plugin->beforeSendPerformed($event);
            }
        }
    }

    /**
     * Iterate through registered plugins and execute plugins' methods.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @return void
     */
    protected function sendPerformed(WappoSwift_Mime_SimpleMessage $message)
    {
        $event = new WappoSwift_Events_SendEvent($this, $message);

        foreach ($this->plugins as $plugin) {
            if (method_exists($plugin, 'sendPerformed')) {
                $plugin->sendPerformed($event);
            }
        }
    }

    /**
     * Get the number of recipients.
     *
     * @param  \WappoSwift_Mime_SimpleMessage  $message
     * @return int
     */
    protected function numberOfRecipients(WappoSwift_Mime_SimpleMessage $message)
    {
        return count(array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        ));
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
}
