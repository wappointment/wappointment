<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for all Transfer Encoding schemes.
 *
 * @author Chris Corbyn
 */
interface WappoSwift_Mime_ContentEncoder extends WappoSwift_Encoder
{
    /**
     * Encode $in to $out.
     *
     * @param WappoSwift_OutputByteStream $os              to read from
     * @param WappoSwift_InputByteStream  $is              to write to
     * @param int                    $firstLineOffset
     * @param int                    $maxLineLength   - 0 indicates the default length for this encoding
     */
    public function encodeByteStream(WappoSwift_OutputByteStream $os, WappoSwift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0);

    /**
     * Get the MIME name of this content encoding scheme.
     *
     * @return string
     */
    public function getName();
}
