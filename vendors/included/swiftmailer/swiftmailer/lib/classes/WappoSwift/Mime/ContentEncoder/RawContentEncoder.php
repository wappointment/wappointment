<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Handles raw Transfer Encoding in Swift Mailer.
 *
 * When sending 8-bit content over SMTP, you should use
 * WappoSwift_Transport_Esmtp_EightBitMimeHandler to enable the 8BITMIME SMTP
 * extension.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class WappoSwift_Mime_ContentEncoder_RawContentEncoder implements WappoSwift_Mime_ContentEncoder
{
    /**
     * Encode a given string to produce an encoded string.
     *
     * @param string $string
     * @param int    $firstLineOffset ignored
     * @param int    $maxLineLength   ignored
     *
     * @return string
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        return $string;
    }

    /**
     * Encode stream $in to stream $out.
     *
     * @param int $firstLineOffset ignored
     * @param int $maxLineLength   ignored
     */
    public function encodeByteStream(WappoSwift_OutputByteStream $os, WappoSwift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        while (false !== ($bytes = $os->read(8192))) {
            $is->write($bytes);
        }
    }

    /**
     * Get the name of this encoding scheme.
     *
     * @return string
     */
    public function getName()
    {
        return 'raw';
    }

    /**
     * Not used.
     */
    public function charsetChanged($charset)
    {
    }
}
