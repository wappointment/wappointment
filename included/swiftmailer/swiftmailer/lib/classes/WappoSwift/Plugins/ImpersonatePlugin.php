<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Replaces the sender of a message.
 *
 * @author Arjen Brouwer
 */
class WappoSwift_Plugins_ImpersonatePlugin implements WappoSwift_Events_SendListener
{
    /**
     * The sender to impersonate.
     *
     * @var string
     */
    private $sender;

    /**
     * Create a new ImpersonatePlugin to impersonate $sender.
     *
     * @param string $sender address
     */
    public function __construct($sender)
    {
        $this->sender = $sender;
    }

    /**
     * Invoked immediately before the Message is sent.
     */
    public function beforeSendPerformed(WappoSwift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();

        // save current recipients
        $headers->addPathHeader('X-Swift-Return-Path', $message->getReturnPath());

        // replace them with the one to send to
        $message->setReturnPath($this->sender);
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(WappoSwift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        // restore original headers
        $headers = $message->getHeaders();

        if ($headers->has('X-Swift-Return-Path')) {
            $message->setReturnPath($headers->get('X-Swift-Return-Path')->getAddress());
            $headers->removeAll('X-Swift-Return-Path');
        }
    }
}
