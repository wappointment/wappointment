<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Contains a list of redundant Transports so when one fails, the next is used.
 *
 * @author Chris Corbyn
 */
class WappoSwift_FailoverTransport extends WappoSwift_Transport_FailoverTransport
{
    /**
     * Creates a new FailoverTransport with $transports.
     *
     * @param WappoSwift_Transport[] $transports
     */
    public function __construct($transports = [])
    {
        call_user_func_array(
            [$this, 'WappoSwift_Transport_FailoverTransport::__construct'],
            WappoSwift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.failover')
            );

        $this->setTransports($transports);
    }
}
