<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Redundantly and rotationally uses several Transport implementations when sending.
 *
 * @author Chris Corbyn
 */
class WappoSwift_LoadBalancedTransport extends WappoSwift_Transport_LoadBalancedTransport
{
    /**
     * Creates a new LoadBalancedTransport with $transports.
     *
     * @param array $transports
     */
    public function __construct($transports = [])
    {

        parent::__construct(...WappoSwift_DependencyContainer::getInstance()
            ->createDependenciesFor('transport.loadbalanced'));
            
        $this->setTransports($transports);
    }
}
