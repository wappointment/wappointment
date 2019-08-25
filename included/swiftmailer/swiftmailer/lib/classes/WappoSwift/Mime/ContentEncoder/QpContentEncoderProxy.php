<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Proxy for quoted-printable content encoders.
 *
 * Switches on the best QP encoder implementation for current charset.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 */
class WappoSwift_Mime_ContentEncoder_QpContentEncoderProxy implements WappoSwift_Mime_ContentEncoder
{
    /**
     * @var WappoSwift_Mime_ContentEncoder_QpContentEncoder
     */
    private $safeEncoder;

    /**
     * @var WappoSwift_Mime_ContentEncoder_NativeQpContentEncoder
     */
    private $nativeEncoder;

    /**
     * @var null|string
     */
    private $charset;

    /**
     * Constructor.
     *
     * @param string|null $charset
     */
    public function __construct(WappoSwift_Mime_ContentEncoder_QpContentEncoder $safeEncoder, WappoSwift_Mime_ContentEncoder_NativeQpContentEncoder $nativeEncoder, $charset)
    {
        $this->safeEncoder = $safeEncoder;
        $this->nativeEncoder = $nativeEncoder;
        $this->charset = $charset;
    }

    /**
     * Make a deep copy of object.
     */
    public function __clone()
    {
        $this->safeEncoder = clone $this->safeEncoder;
        $this->nativeEncoder = clone $this->nativeEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function charsetChanged($charset)
    {
        $this->charset = $charset;
        $this->safeEncoder->charsetChanged($charset);
    }

    /**
     * {@inheritdoc}
     */
    public function encodeByteStream(WappoSwift_OutputByteStream $os, WappoSwift_InputByteStream $is, $firstLineOffset = 0, $maxLineLength = 0)
    {
        $this->getEncoder()->encodeByteStream($os, $is, $firstLineOffset, $maxLineLength);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'quoted-printable';
    }

    /**
     * {@inheritdoc}
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        return $this->getEncoder()->encodeString($string, $firstLineOffset, $maxLineLength);
    }

    /**
     * @return WappoSwift_Mime_ContentEncoder
     */
    private function getEncoder()
    {
        return 'utf-8' === $this->charset ? $this->nativeEncoder : $this->safeEncoder;
    }
}
