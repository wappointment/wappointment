<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sends Messages over SMTP with ESMTP support.
 *
 * @author     Chris Corbyn
 *
 * @method WappoSwift_SmtpTransport setUsername(string $username) Set the username to authenticate with.
 * @method string              getUsername()                 Get the username to authenticate with.
 * @method WappoSwift_SmtpTransport setPassword(string $password) Set the password to authenticate with.
 * @method string              getPassword()                 Get the password to authenticate with.
 * @method WappoSwift_SmtpTransport setAuthMode(string $mode)     Set the auth mode to use to authenticate.
 * @method string              getAuthMode()                 Get the auth mode to use to authenticate.
 */
class WappoSwift_SmtpTransport extends WappoSwift_Transport_EsmtpTransport
{
    /**
     * Create a new SmtpTransport, optionally with $host, $port and $security.
     *
     * @param string $host
     * @param int    $port
     * @param string $security
     */
    public function __construct($host = 'localhost', $port = 25, $security = null)
    {
        call_user_func_array(
            [$this, 'WappoSwift_Transport_EsmtpTransport::__construct'],
            WappoSwift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.smtp')
            );

        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($security);
    }
}
