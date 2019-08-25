<?php

namespace Wappointment\Messages;

abstract class AbstractAdminEmail extends AbstractEmail
{
    protected $admin = true;

    public function addLogo()
    {
        $this->addBlock('logo');
    }

    public function addRoundedSquare($lines, $separator = true)
    {
        $this->addBlock('roundedSquare', $lines, $separator);
    }

    public function addButton($title, $action)
    {
        $this->messageBlocks[] = [
            'type' => 'button',
            'content' => $title,
            'action' => $action
        ];
    }

    public function addBlock($type, $lines = [], $separator = true)
    {
        $this->messageBlocks[] = [
            'type' => $type,
            'content' => $lines,
            'separator' => $separator ? 'body-border radius' : ''
        ];
    }
    public function addBr()
    {
        $this->messageBlocks[] = [
            'type' => 'spacer',
        ];
    }
    public function addLines($lines = [])
    {
        $this->messageBlocks[] = [
            'type' => 'content',
            'content' => $lines,
        ];
    }

    public function renderBody()
    {
        if (!empty($this->body)) {
            return $this->finalWrap();
        }
        //get the body as an array
        $this->body = '';
        foreach ($this->messageBlocks as $block) {
            switch ($block['type']) {
                case 'button':
                    $this->body .= $this->renderer->button($block['content'], $block['action']);
                    break;
                case 'roundedSquare':
                    $this->body .= $this->renderer->wrapRoundedSquare($block['content'], $block['separator']);
                    break;
                case 'altRoundedSquare':
                    $this->body .= $this->renderer->wrapAltRoundedSquare($block['content'], $block['separator']);
                    break;
                case 'logo':
                    $this->body .= $this->renderer->logo();
                    break;
                case 'spacer':
                    $this->body .= $this->renderer->spacer();
                    break;

                default:
                    $this->body .= $this->renderer->wrapRow($block['content']);
                    break;
            }
        }

        return $this->finalWrap();
    }
}
