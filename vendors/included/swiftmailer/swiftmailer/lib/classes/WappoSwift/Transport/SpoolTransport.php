<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stores Messages in a queue.
 *
 * @author Fabien Potencier
 */
class WappoSwift_Transport_SpoolTransport implements WappoSwift_Transport
{
    /** The spool instance */
    private $spool;

    /** The event dispatcher from the plugin API */
    private $eventDispatcher;

    /**
     * Constructor.
     */
    public function __construct(WappoSwift_Events_EventDispatcher $eventDispatcher, WappoSwift_Spool $spool = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->spool = $spool;
    }

    /**
     * Sets the spool object.
     *
     * @return $this
     */
    public function setSpool(WappoSwift_Spool $spool)
    {
        $this->spool = $spool;

        return $this;
    }

    /**
     * Get the spool object.
     *
     * @return WappoSwift_Spool
     */
    public function getSpool()
    {
        return $this->spool;
    }

    /**
     * Tests if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Starts this Transport mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Transport mechanism.
     */
    public function stop()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return true;
    }

    /**
     * Sends the given message.
     *
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent e-mail's
     */
    public function send(WappoSwift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $success = $this->spool->queueMessage($message);

        if ($evt) {
            $evt->setResult($success ? WappoSwift_Events_SendEvent::RESULT_SPOOLED : WappoSwift_Events_SendEvent::RESULT_FAILED);
            $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 1;
    }

    /**
     * Register a plugin.
     */
    public function registerPlugin(WappoSwift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }
}
