<?php

namespace Wappointment\Messages;

use Wappointment\Messages\Templates\FoundationEmail;
use Pelago\Emogrifier\CssInliner;
use Wappointment\Services\Settings;
use WappoSwift_Attachment;

abstract class AbstractEmail extends AbstractMessage
{
    use ConvertHtmlToText;

    public $subject = '';
    protected $renderer = null;
    public $messageBlocks = [];
    protected $admin = false;
    public $replacing = ['subject', 'body'];
    public $attachments = [];

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->renderer = new FoundationEmail();

        if (Settings::getStaff('email_logo')) {
            $this->addLogo(Settings::getStaff('email_logo'), 'full');
        }
    }

    public function addLogo($logo = [], $separator = 'body-border radius')
    {
        $this->addBlock('logo', $logo, $separator);
    }

    public function renderSubject()
    {
        return $this->subject;
    }

    public function renderMessage()
    {
        return [
            'subject' => $this->renderSubject(),
            'body' => $this->renderBody(),
            'body_text' => $this->renderBodyText(),
        ];
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
                    $blocks .= $this->renderer->button($block['content'], $block['action'], $block['center']);
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

    public function addBlock($type, $lines = [], $separator = 'body-border radius')
    {
        $this->messageBlocks[] = [
            'type' => $type,
            'content' => $lines,
            'separator' => $separator
        ];
    }

    public function finalWrap()
    {

        if (method_exists($this, 'footerLinks')) {
            $this->body .= $this->footerLinks();
        }
        $this->body = $this->renderer->wrapRow($this->body) . $this->renderer->wrapFooter('');

        $this->parseBody();

        return CssInliner::fromHtml($this->renderer->wrapBoilerPlate($this->body))->inlineCss()->render();
    }

    public function renderBodyText()
    {
        return $this->convertHtmlToText($this->body);
    }

    public function attach($file, array $options = [])
    {
        $attachment = $this->createAttachmentFromPath($file);

        $this->addAttachment($attachment, $options);
    }

    public function attachData($data, $name, array $options = [])
    {
        $attachment = $this->createAttachmentFromData($data, $name);

        $this->addAttachment($attachment, $options);
    }

    protected function createAttachmentFromPath($file)
    {
        if (!file_exists($file)) {
            throw new \WappointmentException("Error Processing Request", 1);
        }
        return WappoSwift_Attachment::fromPath($file);
    }


    protected function createAttachmentFromData($data, $name)
    {
        return new WappoSwift_Attachment($data, $name);
    }

    protected function addAttachment($attachment, $options = [])
    {

        if (!empty($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        if (!empty($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $this->attachments[] = $attachment;
    }
}
