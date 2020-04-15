<?php

namespace Wappointment\Messages;

use Wappointment\Messages\Templates\FoundationEmail;
use Pelago\Emogrifier\CssInliner;
use Wappointment\Services\Settings;

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
        if ($this->admin === false && Settings::getStaff('email_logo')) {
            $this->addLogo(Settings::getStaff('email_logo'), 'full');
            /* $this->renderer->setColors([
                'primary' => 'transparent',
                'primaryLight' => '#7A78D5',
                'separator' => 'transparent',
            ]); */
        }
    }

    public function addLogo($logo = [], $separator = 'body-border radius')
    {
        $this->addBlock('logo', $logo, $separator);
    }
    public function addBlock($type, $lines = [], $separator = 'body-border radius')
    {
        $this->messageBlocks[] = [
            'type' => $type,
            'content' => $lines,
            'separator' => $separator
        ];
    }

    public function renderSubject()
    {
        return $this->subject;
    }

    public function renderBody()
    {
        if (!empty($this->body)) {
            $this->addBlock('default', $this->body);
        }
        $this->body = $this->renderBlocks();
        return $this->finalWrap();
    }

    public function renderBlocks()
    {
        $blocks = '';
        foreach ($this->messageBlocks as $block) {
            switch ($block['type']) {
                case 'button':
                    $blocks .= $this->renderer->button($block['content'], $block['action']);
                    break;
                case 'roundedSquare':
                    $blocks .= $this->renderer->wrapRoundedSquare($block['content'], $block['separator']);
                    break;
                case 'altRoundedSquare':
                    $blocks .= $this->renderer->wrapAltRoundedSquare($block['content'], $block['separator']);
                    break;
                case 'logo':
                    $blocks .= $this->renderer->logo($block['content']);
                    break;
                case 'spacer':
                    $blocks .= $this->renderer->spacer();
                    break;

                default:
                    $blocks .= $this->renderer->wrapRow(isset($block['content']) ? $block['content'] : $block);
                    break;
            }
        }

        return $blocks;
    }

    public function renderMessage()
    {
        $this->renderBody();
    }

    public function finalWrap()
    {

        if (method_exists($this, 'footerLinks')) {
            $this->body .= $this->footerLinks();
        }
        $this->body = $this->renderer->wrapRow($this->body) . $this->renderer->wrapFooter('');
        if (method_exists($this, 'replaceTags')) {
            $this->replaceTags();
        }
        return CssInliner::fromHtml($this->renderer->wrapBoilerPlate($this->body))->inlineCss()->render();
    }

    public function renderBodyText($replaceTags = false)
    {
        if ($replaceTags && method_exists($this, 'replaceTags')) {
            $this->replaceTags();
        }
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
