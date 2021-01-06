<?php

namespace Wappointment\Services\Parser;

/**
 * MimeDir parser.
 *
 * This class par   ses iCalendar 2.0 and vCard 2.1, 3.0 and 4.0 files. This
 * parser will return one of the following two objects from the parse method:
 *
 * Sabre\VObject\Component\VCalendar
 * Sabre\VObject\Component\VCard
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class MimeDir extends \Sabre\VObject\Parser\MimeDir
{
    /**
     * Reads a property or component from a line.
     *
     * @return void
     */
    protected function readProperty($line)
    {
        if ($this->options & self::OPTION_FORGIVING) {
            $propNameToken = 'A-Z0-9\-\._\\/';
        } else {
            $propNameToken = 'A-Z0-9\-\.';
        }

        $paramNameToken = 'A-Z0-9\-';
        $safeChar = '^";:,';
        $qSafeChar = '^"';

        $regex = "/
            ^(?P<name> [$propNameToken]+ ) (?=[;:])        # property name
            |
            (?<=:)(?P<propValue> .+)$                      # property value
            |
            ;(?P<paramName> [$paramNameToken]+) (?=[=;:])  # parameter name
            |
            (=|,)(?P<paramValue>                           # parameter value
                (?: [$safeChar]*) |
                \"(?: [$qSafeChar]+)\"
            ) (?=[;:,])
            /xi";

        //echo $regex, "\n"; die();
        preg_match_all($regex, $line, $matches, PREG_SET_ORDER);

        $property = [
            'name' => null,
            'parameters' => [],
            'value' => null
        ];

        $lastParam = null;

        /**
         * Looping through all the tokens.
         *
         * Note that we are looping through them in reverse order, because if a
         * sub-pattern matched, the subsequent named patterns will not show up
         * in the result.
         */
        foreach ($matches as $match) {
            if (isset($match['paramValue'])) {
                if ($match['paramValue'] && $match['paramValue'][0] === '"') {
                    $value = substr($match['paramValue'], 1, -1);
                } else {
                    $value = $match['paramValue'];
                }

                $value = $this->unescapeParam($value);

                if (is_null($lastParam)) {
                    continue;
                    throw new \Sabre\VObject\ParseException(
                        'Invalid Mimedir file. Line starting at ' .
                            $this->startLine . ' did not follow iCalendar/vCard conventions'
                    );
                }
                if (is_null($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam] = $value;
                } elseif (is_array($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam][] = $value;
                } else {
                    $property['parameters'][$lastParam] = [
                        $property['parameters'][$lastParam],
                        $value
                    ];
                }
                continue;
            }
            if (isset($match['paramName'])) {
                $lastParam = strtoupper($match['paramName']);
                if (!isset($property['parameters'][$lastParam])) {
                    $property['parameters'][$lastParam] = null;
                }
                continue;
            }
            if (isset($match['propValue'])) {
                $property['value'] = $match['propValue'];
                continue;
            }
            if (isset($match['name']) && $match['name']) {
                $property['name'] = strtoupper($match['name']);
                continue;
            }

            // @codeCoverageIgnoreStart
            throw new \LogicException('This code should not be reachable');
            // @codeCoverageIgnoreEnd
        }

        if (is_null($property['value'])) {
            $property['value'] = '';
        }
        if (!$property['name']) {
            if ($this->options & self::OPTION_IGNORE_INVALID_LINES) {
                return false;
            }
            throw new \Sabre\VObject\ParseException(
                'Invalid Mimedir file. Line starting at ' .
                    $this->startLine . ' did not follow iCalendar/vCard conventions'
            );
        }

        // vCard 2.1 states that parameters may appear without a name, and only
        // a value. We can deduce the value based on it's name.
        //
        // Our parser will get those as parameters without a value instead, so
        // we're filtering these parameters out first.
        $namedParameters = [];
        $namelessParameters = [];

        foreach ($property['parameters'] as $name => $value) {
            if (!is_null($value)) {
                $namedParameters[$name] = $value;
            } else {
                $namelessParameters[] = $name;
            }
        }

        $propObj = $this->root->createProperty($property['name'], null, $namedParameters);

        foreach ($namelessParameters as $namelessParameter) {
            $propObj->add(null, $namelessParameter);
        }

        if (strtoupper($propObj['ENCODING']) === 'QUOTED-PRINTABLE') {
            $propObj->setQuotedPrintableValue($this->extractQuotedPrintableValue());
        } else {
            $charset = $this->charset;
            if ($this->root->getDocumentType() === \Sabre\VObject\Document::VCARD21 && isset($propObj['CHARSET'])) {
                // vCard 2.1 allows the character set to be specified per property.
                $charset = (string) $propObj['CHARSET'];
            }
            switch (strtolower($charset)) {
                case 'utf-8':
                    break;
                case 'iso-8859-1':
                    $property['value'] = utf8_encode($property['value']);
                    break;
                case 'windows-1252':
                    $property['value'] = mb_convert_encoding($property['value'], 'UTF-8', $charset);
                    break;
                default:
                    throw new \Sabre\VObject\ParseException('Unsupported CHARSET: ' . $propObj['CHARSET']);
            }
            $propObj->setRawMimeDirValue($property['value']);
        }

        return $propObj;
    }

    /**
     * Unescapes a parameter value.
     *
     * vCard 2.1:
     *   * Does not mention a mechanism for this. In addition, double quotes
     *     are never used to wrap values.
     *   * This means that parameters can simply not contain colons or
     *     semi-colons.
     *
     * vCard 3.0 (rfc2425, rfc2426):
     *   * Parameters _may_ be surrounded by double quotes.
     *   * If this is not the case, semi-colon, colon and comma may simply not
     *     occur (the comma used for multiple parameter values though).
     *   * If it is surrounded by double-quotes, it may simply not contain
     *     double-quotes.
     *   * This means that a parameter can in no case encode double-quotes, or
     *     newlines.
     *
     * vCard 4.0 (rfc6350)
     *   * Behavior seems to be identical to vCard 3.0
     *
     * iCalendar 2.0 (rfc5545)
     *   * Behavior seems to be identical to vCard 3.0
     *
     * Parameter escaping mechanism (rfc6868) :
     *   * This rfc describes a new way to escape parameter values.
     *   * New-line is encoded as ^n
     *   * ^ is encoded as ^^.
     *   * " is encoded as ^'
     *
     * @param string $input
     *
     * @return void
     */
    private function unescapeParam($input)
    {
        return
            preg_replace_callback(
                '#(\^(\^|n|\'))#',
                function ($matches) {
                    switch ($matches[2]) {
                        case 'n':
                            return "\n";
                        case '^':
                            return '^';
                        case '\'':
                            return '"';

                            // @codeCoverageIgnoreStart
                    }
                    // @codeCoverageIgnoreEnd
                },
                $input
            );
    }

    /**
     * Gets the full quoted printable value.
     *
     * We need a special method for this, because newlines have both a meaning
     * in vCards, and in QuotedPrintable.
     *
     * This method does not do any decoding.
     *
     * @return string
     */
    private function extractQuotedPrintableValue()
    {
        // We need to parse the raw line again to get the start of the value.
        //
        // We are basically looking for the first colon (:), but we need to
        // skip over the parameters first, as they may contain one.
        $regex = '/^
            (?: [^:])+ # Anything but a colon
            (?: "[^"]")* # A parameter in double quotes
            : # start of the value we really care about
            (.*)$
        /xs';

        preg_match($regex, $this->rawLine, $matches);

        $value = $matches[1];
        // Removing the first whitespace character from every line. Kind of
        // like unfolding, but we keep the newline.
        $value = str_replace("\n ", "\n", $value);

        // Microsoft products don't always correctly fold lines, they may be
        // missing a whitespace. So if 'forgiving' is turned on, we will take
        // those as well.
        if ($this->options & self::OPTION_FORGIVING) {
            while (substr($value, -1) === '=') {
                // Reading the line
                $this->readLine();
                // Grabbing the raw form
                $value .= "\n" . $this->rawLine;
            }
        }

        return $value;
    }

    protected function parseDocument()
    {
        $line = $this->readLine();

        // BOM is ZERO WIDTH NO-BREAK SPACE (U+FEFF).
        // It's 0xEF 0xBB 0xBF in UTF-8 hex.
        if (3 <= strlen($line) && ord($line[0]) === 0xef && ord($line[1]) === 0xbb && ord($line[2]) === 0xbf) {
            $line = substr($line, 3);
        }

        switch (strtoupper($line)) {
            case 'BEGIN:VCALENDAR':
                $class = \Sabre\VObject\Component\VCalendar::$componentMap['VCALENDAR'];
                break;
            case 'BEGIN:VCARD':
                $class = \Sabre\VObject\Component\VCard::$componentMap['VCARD'];
                break;
            default:
                //echo 'hello - '.$line;
                return;
                throw new \Sabre\VObject\ParseException('This parser only supports VCARD and VCALENDAR files');
        }

        $this->root = new $class([], false);

        while (true) {
            // Reading until we hit END:
            $line = $this->readLine();
            if (strtoupper(substr($line, 0, 4)) === 'END:') {
                break;
            }
            $result = $this->parseLine($line);
            if ($result) {
                $this->root->add($result);
            }
        }

        $name = strtoupper(substr($line, 4));
        if ($name !== $this->root->name) {
            throw new \Sabre\VObject\ParseException(
                'Invalid MimeDir file. expected: "END:' .
                    $this->root->name . '" got: "END:' . $name . '"'
            );
        }
    }
}
