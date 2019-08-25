<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for the EventDispatcher which handles the event dispatching layer.
 *
 * @author Chris Corbyn
 */
interface WappoSwift_Events_EventDispatcher
{
    /**
     * Create a new SendEvent for $source and $message.
     *
     * @param WappoSwift_Transport $source
     * @param WappoSwift_Mime_SimpleMessage
     *
     * @return WappoSwift_Events_SendEvent
     */
    public function createSendEvent(WappoSwift_Transport $source, WappoSwift_Mime_SimpleMessage $message);

    /**
     * Create a new CommandEvent for $source and $command.
     *
     * @param WappoSwift_Transport $source
     * @param string          $command      That will be executed
     * @param array           $successCodes That are needed
     *
     * @return WappoSwift_Events_CommandEvent
     */
    public function createCommandEvent(WappoSwift_Transport $source, $command, $successCodes = []);

    /**
     * Create a new ResponseEvent for $source and $response.
     *
     * @param WappoSwift_Transport $source
     * @param string          $response
     * @param bool            $valid    If the response is valid
     *
     * @return WappoSwift_Events_ResponseEvent
     */
    public function createResponseEvent(WappoSwift_Transport $source, $response, $valid);

    /**
     * Create a new TransportChangeEvent for $source.
     *
     * @param WappoSwift_Transport $source
     *
     * @return WappoSwift_Events_TransportChangeEvent
     */
    public function createTransportChangeEvent(WappoSwift_Transport $source);

    /**
     * Create a new TransportExceptionEvent for $source.
     *
     * @param WappoSwift_Transport          $source
     * @param WappoSwift_TransportException $ex
     *
     * @return WappoSwift_Events_TransportExceptionEvent
     */
    public function createTransportExceptionEvent(WappoSwift_Transport $source, WappoSwift_TransportException $ex);

    /**
     * Bind an event listener to this dispatcher.
     *
     * @param WappoSwift_Events_EventListener $listener
     */
    public function bindEventListener(WappoSwift_Events_EventListener $listener);

    /**
     * Dispatch the given Event to all suitable listeners.
     *
     * @param WappoSwift_Events_EventObject $evt
     * @param string                   $target method
     */
    public function dispatchEvent(WappoSwift_Events_EventObject $evt, $target);
}
