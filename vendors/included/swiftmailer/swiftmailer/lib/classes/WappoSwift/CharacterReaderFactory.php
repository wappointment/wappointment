<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A factory for creating CharacterReaders.
 *
 * @author Chris Corbyn
 */
interface WappoSwift_CharacterReaderFactory
{
    /**
     * Returns a CharacterReader suitable for the charset applied.
     *
     * @param string $charset
     *
     * @return WappoSwift_CharacterReader
     */
    public function getReaderFor($charset);
}
