<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Listens for changes within the Transport system.
 *
 * @author Chris Corbyn
 */
interface WappoSwift_Events_TransportChangeListener extends WappoSwift_Events_EventListener
{
    /**
     * Invoked just before a Transport is started.
     *
     * @param WappoSwift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStarted(WappoSwift_Events_TransportChangeEvent $evt);

    /**
     * Invoked immediately after the Transport is started.
     *
     * @param WappoSwift_Events_TransportChangeEvent $evt
     */
    public function transportStarted(WappoSwift_Events_TransportChangeEvent $evt);

    /**
     * Invoked just before a Transport is stopped.
     *
     * @param WappoSwift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStopped(WappoSwift_Events_TransportChangeEvent $evt);

    /**
     * Invoked immediately after the Transport is stopped.
     *
     * @param WappoSwift_Events_TransportChangeEvent $evt
     */
    public function transportStopped(WappoSwift_Events_TransportChangeEvent $evt);
}
