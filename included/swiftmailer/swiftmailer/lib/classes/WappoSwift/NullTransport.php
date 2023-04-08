<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author Fabien Potencier
 */
class WappoSwift_NullTransport extends WappoSwift_Transport_NullTransport
{
    public function __construct()
    {
        parent::__construct(...WappoSwift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.null'));
    }
}
