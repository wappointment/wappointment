<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An embedded file, in a multipart message.
 *
 * @author Chris Corbyn
 */
class WappoSwift_Mime_EmbeddedFile extends WappoSwift_Mime_Attachment
{
    /**
     * Creates a new Attachment with $headers and $encoder.
     *
     * @param array $mimeTypes optional
     */
    public function __construct(WappoSwift_Mime_SimpleHeaderSet $headers, WappoSwift_Mime_ContentEncoder $encoder, WappoSwift_KeyCache $cache, WappoSwift_IdGenerator $idGenerator, $mimeTypes = [])
    {
        parent::__construct($headers, $encoder, $cache, $idGenerator, $mimeTypes);
        $this->setDisposition('inline');
        $this->setId($this->getId());
    }

    /**
     * Get the nesting level of this EmbeddedFile.
     *
     * Returns {@see LEVEL_RELATED}.
     *
     * @return int
     */
    public function getNestingLevel()
    {
        return self::LEVEL_RELATED;
    }
}
