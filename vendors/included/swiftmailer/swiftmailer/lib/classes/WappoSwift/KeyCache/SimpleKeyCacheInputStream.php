<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Writes data to a KeyCache using a stream.
 *
 * @author Chris Corbyn
 */
class WappoSwift_KeyCache_SimpleKeyCacheInputStream implements WappoSwift_KeyCache_KeyCacheInputStream
{
    /** The KeyCache being written to */
    private $keyCache;

    /** The nsKey of the KeyCache being written to */
    private $nsKey;

    /** The itemKey of the KeyCache being written to */
    private $itemKey;

    /** A stream to write through on each write() */
    private $writeThrough = null;

    /**
     * Set the KeyCache to wrap.
     */
    public function setKeyCache(WappoSwift_KeyCache $keyCache)
    {
        $this->keyCache = $keyCache;
    }

    /**
     * Specify a stream to write through for each write().
     */
    public function setWriteThroughStream(WappoSwift_InputByteStream $is)
    {
        $this->writeThrough = $is;
    }

    /**
     * Writes $bytes to the end of the stream.
     *
     * @param string                $bytes
     * @param WappoSwift_InputByteStream $is    optional
     */
    public function write($bytes, WappoSwift_InputByteStream $is = null)
    {
        $this->keyCache->setString(
            $this->nsKey, $this->itemKey, $bytes, WappoSwift_KeyCache::MODE_APPEND
            );
        if (isset($is)) {
            $is->write($bytes);
        }
        if (isset($this->writeThrough)) {
            $this->writeThrough->write($bytes);
        }
    }

    /**
     * Not used.
     */
    public function commit()
    {
    }

    /**
     * Not used.
     */
    public function bind(WappoSwift_InputByteStream $is)
    {
    }

    /**
     * Not used.
     */
    public function unbind(WappoSwift_InputByteStream $is)
    {
    }

    /**
     * Flush the contents of the stream (empty it) and set the internal pointer
     * to the beginning.
     */
    public function flushBuffers()
    {
        $this->keyCache->clearKey($this->nsKey, $this->itemKey);
    }

    /**
     * Set the nsKey which will be written to.
     *
     * @param string $nsKey
     */
    public function setNsKey($nsKey)
    {
        $this->nsKey = $nsKey;
    }

    /**
     * Set the itemKey which will be written to.
     *
     * @param string $itemKey
     */
    public function setItemKey($itemKey)
    {
        $this->itemKey = $itemKey;
    }

    /**
     * Any implementation should be cloneable, allowing the clone to access a
     * separate $nsKey and $itemKey.
     */
    public function __clone()
    {
        $this->writeThrough = null;
    }
}
