<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generated when a TransportException is thrown from the Transport system.
 *
 * @author Chris Corbyn
 */
class WappoSwift_Events_TransportExceptionEvent extends WappoSwift_Events_EventObject
{
    /**
     * The Exception thrown.
     *
     * @var WappoSwift_TransportException
     */
    private $exception;

    /**
     * Create a new TransportExceptionEvent for $transport.
     */
    public function __construct(WappoSwift_Transport $transport, WappoSwift_TransportException $ex)
    {
        parent::__construct($transport);
        $this->exception = $ex;
    }

    /**
     * Get the TransportException thrown.
     *
     * @return WappoSwift_TransportException
     */
    public function getException()
    {
        return $this->exception;
    }
}
