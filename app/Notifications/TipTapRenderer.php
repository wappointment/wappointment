<?php
declare(strict_types=1);

namespace Wappointment\Notifications;

/**
 * Renders a stored reminder body (`wappo_reminders.options.body`, a TipTap document) to
 * email HTML. Follows legacy master `Helpers\TipTap::toHTML` and `Reminder::filterBody`:
 * same element mapping, `paragraph` inside a list item becomes a span, an empty
 * paragraph or heading is a blank line (`<p><br/></p>`), conditional blocks show only for the matching appointment type, and a
 * `customfield` node is a `[src:alt]` tag. Unlike legacy, text, tag values and link
 * targets are escaped, and nodes or marks the renderer does not know fail visibly
 * instead of producing broken markup.
 */
final class TipTapRenderer
{
    /** Legacy `Appointment::TYPE_*` values stored in `wappo_appointments.type`. */
    public const TYPE_PHYSICAL = 0;
    public const TYPE_PHONE = 1;
    public const TYPE_ZOOM = 5;

    private const CONDITIONAL_BLOCKS = [
        'cblockphysical' => self::TYPE_PHYSICAL,
        'cblockphone' => self::TYPE_PHONE,
        'cblockzoom' => self::TYPE_ZOOM,
    ];

    private const BLOCKS = [
        'bullet_list' => 'ul',
        'ordered_list' => 'ol',
        'list_item' => 'li',
        'blockquote' => 'blockquote',
        'code_block' => 'pre',
    ];

    private const MARKS = ['bold' => 'strong', 'italic' => 'i', 'underline' => 'u', 'strike' => 's', 'code' => 'code'];

    private const MAX_DEPTH = 32;

    public function __construct(private EmailTags $tags, private int $appointmentType) {}

    public static function isDocument(mixed $body): bool
    {
        return is_array($body) && ($body['type'] ?? null) === 'doc' && is_array($body['content'] ?? null);
    }

    public function render(mixed $body): string
    {
        if (!self::isDocument($body)) {
            throw new InvalidTemplateException('The email template body is not a stored editor document.');
        }
        return $this->children($body, null, 0);
    }

    private function children(array $node, ?string $parentTag, int $depth): string
    {
        if ($depth > self::MAX_DEPTH) {
            throw new InvalidTemplateException('The email template is nested too deeply.');
        }
        $content = $node['content'] ?? [];
        if (!is_array($content)) {
            throw new InvalidTemplateException('The email template contains malformed content.');
        }
        $html = '';
        foreach ($content as $child) {
            if (!is_array($child) || !is_string($child['type'] ?? null)) {
                throw new InvalidTemplateException('The email template contains a malformed node.');
            }
            $html .= $this->node($child, $parentTag, $depth + 1);
        }
        return $html;
    }

    private function node(array $node, ?string $parentTag, int $depth): string
    {
        $type = $node['type'];
        if (isset(self::CONDITIONAL_BLOCKS[$type])) {
            return self::CONDITIONAL_BLOCKS[$type] === $this->appointmentType ? $this->children($node, $parentTag, $depth) : '';
        }
        switch ($type) {
            case 'paragraph':
                $tag = $parentTag === 'li' ? 'span' : 'p';
                $inner = $this->children($node, $tag, $depth);
                return '<' . $tag . '>' . ($inner === '' ? '<br/>' : $inner) . '</' . $tag . '>';
            case 'heading':
                $level = $node['attrs']['level'] ?? null;
                if (!is_int($level) || $level < 1 || $level > 6) {
                    throw new InvalidTemplateException('The email template contains a heading without a valid level.');
                }
                $inner = $this->children($node, 'h' . $level, $depth);
                return '<h' . $level . '>' . ($inner === '' ? '<br/>' : $inner) . '</h' . $level . '>';
            case 'hard_break':
                return '<br/>';
            case 'horizontal_rule':
                return '<hr/>';
            case 'text':
                $text = $node['text'] ?? '';
                if (!is_string($text)) {
                    throw new InvalidTemplateException('The email template contains malformed text.');
                }
                return $this->marks($node, $this->tags->replaceHtml($text));
            case 'customfield':
                $src = $node['attrs']['src'] ?? null;
                $alt = $node['attrs']['alt'] ?? null;
                if (!is_string($src) || !is_string($alt)) {
                    throw new InvalidTemplateException('The email template contains a malformed tag.');
                }
                return $this->marks($node, $this->tags->replaceHtml('[' . $src . ':' . $alt . ']'));
        }
        if (isset(self::BLOCKS[$type])) {
            $tag = self::BLOCKS[$type];
            return '<' . $tag . '>' . $this->children($node, $tag, $depth) . '</' . $tag . '>';
        }
        throw new InvalidTemplateException('The email template contains an unsupported "' . $type . '" element.');
    }

    private function marks(array $node, string $html): string
    {
        $marks = $node['marks'] ?? [];
        if (!is_array($marks)) {
            throw new InvalidTemplateException('The email template contains malformed formatting.');
        }
        foreach ($marks as $mark) {
            $type = is_array($mark) ? ($mark['type'] ?? null) : null;
            if ($type === 'link') {
                $html = $this->link($mark, $html);
                continue;
            }
            if (!is_string($type) || !isset(self::MARKS[$type])) {
                throw new InvalidTemplateException('The email template contains unsupported formatting.');
            }
            $html = '<' . self::MARKS[$type] . '>' . $html . '</' . self::MARKS[$type] . '>';
        }
        return $html;
    }

    private function link(array $mark, string $html): string
    {
        $href = $mark['attrs']['href'] ?? '';
        if (!is_string($href)) {
            throw new InvalidTemplateException('The email template contains a malformed link.');
        }
        $href = trim($href);
        if (EmailTags::isTag($href)) {
            $href = $this->tags->value(substr($href, 1, -1)) ?? '';
        }
        $url = Html::url($href);
        // A link whose target is unavailable keeps its text rather than pointing nowhere.
        return $url === null ? $html : '<a href="' . $url . '" target="_blank">' . $html . '</a>';
    }
}
