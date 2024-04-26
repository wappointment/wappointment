<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Does real time reporting of pass/fail for each recipient.
 *
 * @author Chris Corbyn
 */
class WappoSwift_Plugins_ReporterPlugin implements WappoSwift_Events_SendListener
{
    /**
     * The reporter backend which takes notifications.
     *
     * @var WappoSwift_Plugins_Reporter
     */
    private $reporter;

    /**
     * Create a new ReporterPlugin using $reporter.
     */
    public function __construct(WappoSwift_Plugins_Reporter $reporter)
    {
        $this->reporter = $reporter;
    }

    /**
     * Not used.
     */
    public function beforeSendPerformed(WappoSwift_Events_SendEvent $evt)
    {
    }

    /**
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(WappoSwift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $failures = array_flip($evt->getFailedRecipients());
        foreach ((array) $message->getTo() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? WappoSwift_Plugins_Reporter::RESULT_FAIL : WappoSwift_Plugins_Reporter::RESULT_PASS));
        }
        foreach ((array) $message->getCc() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? WappoSwift_Plugins_Reporter::RESULT_FAIL : WappoSwift_Plugins_Reporter::RESULT_PASS));
        }
        foreach ((array) $message->getBcc() as $address => $null) {
            $this->reporter->notify($message, $address, (array_key_exists($address, $failures) ? WappoSwift_Plugins_Reporter::RESULT_FAIL : WappoSwift_Plugins_Reporter::RESULT_PASS));
        }
    }
}
