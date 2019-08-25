<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Header Signer Interface used to apply Header-Based Signature to a message.
 *
 * @author Xavier De Cock <xdecock@gmail.com>
 */
interface WappoSwift_Signers_HeaderSigner extends WappoSwift_Signer, WappoSwift_InputByteStream
{
    /**
     * Exclude an header from the signed headers.
     *
     * @param string $header_name
     *
     * @return self
     */
    public function ignoreHeader($header_name);

    /**
     * Prepare the Signer to get a new Body.
     *
     * @return self
     */
    public function startBody();

    /**
     * Give the signal that the body has finished streaming.
     *
     * @return self
     */
    public function endBody();

    /**
     * Give the headers already given.
     *
     * @param WappoSwift_Mime_SimpleHeaderSet $headers
     *
     * @return self
     */
    public function setHeaders(WappoSwift_Mime_SimpleHeaderSet $headers);

    /**
     * Add the header(s) to the headerSet.
     *
     * @param WappoSwift_Mime_SimpleHeaderSet $headers
     *
     * @return self
     */
    public function addSignature(WappoSwift_Mime_SimpleHeaderSet $headers);

    /**
     * Return the list of header a signer might tamper.
     *
     * @return array
     */
    public function getAlteredHeaders();
}
