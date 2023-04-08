<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The Message class for building emails.
 *
 * @author Chris Corbyn
 */
class WappoSwift_Message extends WappoSwift_Mime_SimpleMessage
{
    /**
     * @var WappoSwift_Signers_HeaderSigner[]
     */
    private $headerSigners = [];

    /**
     * @var WappoSwift_Signers_BodySigner[]
     */
    private $bodySigners = [];

    /**
     * @var array
     */
    private $savedMessage = [];

    /**
     * Create a new Message.
     *
     * Details may be optionally passed into the constructor.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     */
    public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
    {

        parent::__construct(...WappoSwift_DependencyContainer::getInstance()
        ->createDependenciesFor('mime.message'));

        if (!isset($charset)) {
            $charset = WappoSwift_DependencyContainer::getInstance()
                ->lookup('properties.charset');
        }
        $this->setSubject($subject);
        $this->setBody($body);
        $this->setCharset($charset);
        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Add a MimePart to this Message.
     *
     * @param string|WappoSwift_OutputByteStream $body
     * @param string                        $contentType
     * @param string                        $charset
     *
     * @return $this
     */
    public function addPart($body, $contentType = null, $charset = null)
    {
        return $this->attach((new WappoSwift_MimePart($body, $contentType, $charset))->setEncoder($this->getEncoder()));
    }

    /**
     * Attach a new signature handler to the message.
     *
     * @return $this
     */
    public function attachSigner(WappoSwift_Signer $signer)
    {
        if ($signer instanceof WappoSwift_Signers_HeaderSigner) {
            $this->headerSigners[] = $signer;
        } elseif ($signer instanceof WappoSwift_Signers_BodySigner) {
            $this->bodySigners[] = $signer;
        }

        return $this;
    }

    /**
     * Detach a signature handler from a message.
     *
     * @return $this
     */
    public function detachSigner(WappoSwift_Signer $signer)
    {
        if ($signer instanceof WappoSwift_Signers_HeaderSigner) {
            foreach ($this->headerSigners as $k => $headerSigner) {
                if ($headerSigner === $signer) {
                    unset($this->headerSigners[$k]);

                    return $this;
                }
            }
        } elseif ($signer instanceof WappoSwift_Signers_BodySigner) {
            foreach ($this->bodySigners as $k => $bodySigner) {
                if ($bodySigner === $signer) {
                    unset($this->bodySigners[$k]);

                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all signature handlers attached to the message.
     *
     * @return $this
     */
    public function clearSigners()
    {
        $this->headerSigners = [];
        $this->bodySigners = [];

        return $this;
    }

    /**
     * Get this message as a complete string.
     *
     * @return string
     */
    public function toString()
    {
        if (empty($this->headerSigners) && empty($this->bodySigners)) {
            return parent::toString();
        }

        $this->saveMessage();

        $this->doSign();

        $string = parent::toString();

        $this->restoreMessage();

        return $string;
    }

    /**
     * Write this message to a {@link WappoSwift_InputByteStream}.
     */
    public function toByteStream(WappoSwift_InputByteStream $is)
    {
        if (empty($this->headerSigners) && empty($this->bodySigners)) {
            parent::toByteStream($is);

            return;
        }

        $this->saveMessage();

        $this->doSign();

        parent::toByteStream($is);

        $this->restoreMessage();
    }

    public function __wakeup()
    {
        WappoSwift_DependencyContainer::getInstance()->createDependenciesFor('mime.message');
    }

    /**
     * loops through signers and apply the signatures.
     */
    protected function doSign()
    {
        foreach ($this->bodySigners as $signer) {
            $altered = $signer->getAlteredHeaders();
            $this->saveHeaders($altered);
            $signer->signMessage($this);
        }

        foreach ($this->headerSigners as $signer) {
            $altered = $signer->getAlteredHeaders();
            $this->saveHeaders($altered);
            $signer->reset();

            $signer->setHeaders($this->getHeaders());

            $signer->startBody();
            $this->bodyToByteStream($signer);
            $signer->endBody();

            $signer->addSignature($this->getHeaders());
        }
    }

    /**
     * save the message before any signature is applied.
     */
    protected function saveMessage()
    {
        $this->savedMessage = ['headers' => []];
        $this->savedMessage['body'] = $this->getBody();
        $this->savedMessage['children'] = $this->getChildren();
        if (count($this->savedMessage['children']) > 0 && '' != $this->getBody()) {
            $this->setChildren(array_merge([$this->becomeMimePart()], $this->savedMessage['children']));
            $this->setBody('');
        }
    }

    /**
     * save the original headers.
     */
    protected function saveHeaders(array $altered)
    {
        foreach ($altered as $head) {
            $lc = strtolower($head);

            if (!isset($this->savedMessage['headers'][$lc])) {
                $this->savedMessage['headers'][$lc] = $this->getHeaders()->getAll($head);
            }
        }
    }

    /**
     * Remove or restore altered headers.
     */
    protected function restoreHeaders()
    {
        foreach ($this->savedMessage['headers'] as $name => $savedValue) {
            $headers = $this->getHeaders()->getAll($name);

            foreach ($headers as $key => $value) {
                if (!isset($savedValue[$key])) {
                    $this->getHeaders()->remove($name, $key);
                }
            }
        }
    }

    /**
     * Restore message body.
     */
    protected function restoreMessage()
    {
        $this->setBody($this->savedMessage['body']);
        $this->setChildren($this->savedMessage['children']);

        $this->restoreHeaders();
        $this->savedMessage = [];
    }

    /**
     * Clone Message Signers.
     *
     * @see WappoSwift_Mime_SimpleMimeEntity::__clone()
     */
    public function __clone()
    {
        parent::__clone();
        foreach ($this->bodySigners as $key => $bodySigner) {
            $this->bodySigners[$key] = clone $bodySigner;
        }

        foreach ($this->headerSigners as $key => $headerSigner) {
            $this->headerSigners[$key] = clone $headerSigner;
        }
    }
}
