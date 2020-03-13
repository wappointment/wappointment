<?php

namespace Wappointment\Messages;

use Wappointment\Messages\Templates\FoundationEmail;
use Pelago\Emogrifier\CssInliner;

abstract class AbstractEmail extends AbstractMessage
{
    public $subject = '';
    public $body = '';
    protected $renderer = null;
    protected $messageBlocks = [];
    protected $admin = false;

    public function __construct(...$params)
    {
        $this->renderer = new FoundationEmail($this->admin);
        $this->loadEmail(...$params);
    }

    public function renderSubject()
    {
        return $this->subject;
    }

    public function renderBody()
    {
        if (!empty($this->body)) {
            return $this->finalWrap();
        }
        //get the body as an array
        $this->body = '';
        foreach ($this->messageBlocks as $block) {
            $this->body .= $this->renderer->wrapRow($block);
        }

        return $this->finalWrap();
    }

    public function renderMessage()
    {
        $this->renderBody();
    }

    public function finalWrap()
    {
        $this->body = $this->renderer->wrapRow($this->body);
        if (method_exists($this, 'footerLinks')) {
            $this->body .= $this->renderer->wrapFooter($this->footerLinks());
        }
        if (method_exists($this, 'replaceTags')) {
            $this->replaceTags();
        }
        return CssInliner::fromHtml($this->renderer->wrapBoilerPlate($this->body))->inlineCss()->render();
    }

    public function renderBodyText()
    {
        return $this->convertHtmlToText($this->body);
    }

    private function convertHtmlToText($html, $fullConvert = true)
    {
        if ($fullConvert) {
            $html = preg_replace('# +#', ' ', $html);
            $html = str_replace(["\n", "\r", "\t"], '', $html);
        }
        $removepictureslinks = "#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU";
        $removeScript = '#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU';
        $removeStyle = '/<style\\b[^>]*>(.*?)<\\/style>/s';
        $removeStrikeTags = '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
        $replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
        $replaceByStars = '#< *li[^>]*>#Ui';
        $replaceByReturnChar1 = '#< */ *(li|td|tr|div|p)[^>]*> *< *(li|td|tr|div|p)[^>]*>#Ui';
        $replaceByReturnChar = '#< */? *(br|p|h1|h2|legend|h3|li|ul|h4|h5|h6|tr|td|div)[^>]*>#Ui';
        $replaceLinks = '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.*)< *\/ *a *>/Uis';
        $text = preg_replace(
            [
                $removepictureslinks, $removeScript, $removeStyle, $removeStrikeTags,
                $replaceByTwoReturnChar, $replaceByStars, $replaceByReturnChar1,
                $replaceByReturnChar, $replaceLinks
            ],
            ['', '', '', '', "\n\n", "\n* ", "\n", "\n", '${2} ( ${1} )'],
            $html
        );
        $text = str_replace(['Â ', '&nbsp;'], ' ', strip_tags($text));
        $text = trim(@html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        if ($fullConvert) {
            $text = preg_replace('# +#', ' ', $text);
            $text = preg_replace('#\n *\n\s+#', "\n\n", $text);
        }
        return $text;
    }
}
