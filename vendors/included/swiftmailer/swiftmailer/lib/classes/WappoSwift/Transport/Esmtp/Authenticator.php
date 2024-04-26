<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An Authentication mechanism.
 *
 * @author Chris Corbyn
 */
interface WappoSwift_Transport_Esmtp_Authenticator
{
    /**
     * Get the name of the AUTH mechanism this Authenticator handles.
     *
     * @return string
     */
    public function getAuthKeyword();

    /**
     * Try to authenticate the user with $username and $password.
     *
     * @param WappoSwift_Transport_SmtpAgent $agent
     * @param string                    $username
     * @param string                    $password
     *
     * @return bool true if authentication worked (returning false is deprecated, throw a WappoSwift_TransportException instead)
     *
     * @throws WappoSwift_TransportException Allows the message to bubble up when authentication was not successful
     */
    public function authenticate(WappoSwift_Transport_SmtpAgent $agent, $username, $password);
}
