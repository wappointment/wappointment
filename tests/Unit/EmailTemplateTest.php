<?php
declare(strict_types=1);

use Wappointment\Notifications\AdminEmail;
use Wappointment\Notifications\CustomerEmail;
use Wappointment\Notifications\EmailContext;
use Wappointment\Notifications\EmailTags;
use Wappointment\Notifications\HtmlToText;
use Wappointment\Notifications\InvalidTemplateException;
use Wappointment\Notifications\TipTapRenderer;

if (!getenv('WAPPOINTMENT_WP_ROOT') && !function_exists('__')) {
    function __(string $message, string $domain = ''): string
    {
        return $message;
    }
}

function emailContext(array $overrides = []): EmailContext
{
    return new EmailContext(...($overrides + [
        'clientName' => 'Ada <script>', 'clientEmail' => 'ada@example.invalid', 'clientPhone' => '+32 1',
        'serviceName' => 'Consult', 'serviceAddress' => '1 Main St', 'locationName' => 'Office',
        'appointmentType' => TipTapRenderer::TYPE_PHYSICAL, 'staffName' => 'Bob Staff', 'staffFirstName' => 'Bob',
        'start' => 1767261600, 'end' => 1767265200, 'previousStart' => 1767175200, 'previousEnd' => 1767178800,
        'timezone' => 'Europe/Brussels',
        'links' => [
            'add_to_calendar' => 'https://example.invalid/book/?view=add-event-to-calendar&appointmentkey=k',
            'reschedule' => 'https://example.invalid/book/?view=reschedule-event&appointmentkey=k',
            'cancel' => 'https://example.invalid/book/?view=cancel-event&appointmentkey=k',
            'view' => null, 'new' => 'https://example.invalid/book/?view=new-event', 'new_staff' => null,
        ],
        'formats' => ['date_format' => 'F j, Y', 'time_format' => 'H:i', 'date_time_union' => ' - '],
    ]));
}

function emailDisplay(array $overrides = []): array
{
    return $overrides + [
        'footer' => true, 'test' => false, 'rtl' => false, 'logo_url' => 'https://example.invalid/logo.png',
        'link_color' => '#123456', 'site_url' => 'https://example.invalid', 'email_footer' => "Line 1\nLine <b>2</b>",
        'allow_rescheduling' => true, 'allow_cancellation' => false,
        'labels' => ['calendar' => 'Save to calendar', 'reschedule' => 'Reschedule', 'cancel' => 'Cancel'],
    ];
}

/** The body legacy master seeds for the "canceled" event (`SeedData` through `TipTap::simpleArrayToTipTap`). */
function legacyCanceledBody(): array
{
    $paragraph = static fn(array $content = []): array => $content === [] ? ['type' => 'paragraph'] : ['type' => 'paragraph', 'content' => $content];
    $text = static fn(string $value): array => ['type' => 'text', 'text' => $value];
    $tag = static fn(string $model, string $key): array => ['type' => 'customfield', 'attrs' => ['src' => $model, 'alt' => $key, 'class' => 'customfield', 'title' => null]];
    return ['type' => 'doc', 'content' => [
        $paragraph([$text('Dear '), $tag('client', 'name'), $text(',')]),
        $paragraph(),
        $paragraph([$text('Your appointment taking place the '), $tag('appointment', 'starts'), $text(' has been cancelled.')]),
        $paragraph([$text('If you want to book a new appointment with us, '),
            ['type' => 'text', 'text' => 'click here', 'marks' => [['type' => 'link', 'attrs' => ['href' => '[appointment:linkNew]']]]], $text('.')]),
        $paragraph(),
        $paragraph([$text('We hope to see you soon!')]),
        $paragraph([$text('My <Blog>')]),
    ]];
}

test('legacy seeded templates render with escaped tag values, blank lines and tag links', function () {
    $email = CustomerEmail::compose(['subject' => 'Hello [client:name] [unknown:tag]', 'body' => legacyCanceledBody()], emailContext(), emailDisplay());

    expect($email->subject)->toBe('Hello Ada <script> [unknown:tag]')
        ->and($email->html)->toContain('<p>Dear Ada &lt;script&gt;,</p>')
        ->and(substr_count($email->html, '<p><br/></p>'))->toBe(2)
        ->and($email->html)->toContain('January 1, 2026 - 11:00 (Europe/Brussels)')
        ->and($email->html)->toContain('<a style="color:#123456" href="https://example.invalid/book/?view=new-event" target="_blank">click here</a>')
        ->and($email->html)->toContain('My &lt;Blog&gt;')
        ->and($email->html)->toContain('src="https://example.invalid/logo.png"')
        ->and($email->text)->toContain("Dear Ada <script>,\n\nYour appointment")
        ->and($email->text)->toContain('click here ( https://example.invalid/book/?view=new-event )')
        ->and($email->text)->not->toContain('<p');
});

test('the footer follows the email footer text and reschedule/cancel settings', function () {
    $email = CustomerEmail::compose(['subject' => 'Confirmed', 'body' => legacyCanceledBody()], emailContext(), emailDisplay());
    expect($email->html)->toContain("Line 1<br>\nLine 2")
        ->and($email->html)->toContain('>Save to calendar</a> | <a')
        ->and($email->html)->toContain('view=reschedule-event')
        ->and($email->html)->not->toContain('view=cancel-event');

    $plain = CustomerEmail::compose(['subject' => 'Pending', 'body' => legacyCanceledBody()], emailContext(),
        emailDisplay(['footer' => false, 'test' => true, 'rtl' => true]));
    expect($plain->subject)->toBe('[TEST_EMAIL]Pending')
        ->and($plain->html)->not->toContain('Save to calendar')
        ->and($plain->html)->toContain('dir="rtl"');
});

test('conditional blocks follow the appointment type and markup is mapped like legacy', function () {
    $doc = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Title']]],
        ['type' => 'cblockphysical', 'content' => [['type' => 'paragraph', 'content' => [
            ['type' => 'text', 'text' => 'Come to '], ['type' => 'customfield', 'attrs' => ['src' => 'service', 'alt' => 'address']],
        ]]]],
        ['type' => 'cblockphone', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'We call you']]]]],
        ['type' => 'bullet_list', 'content' => [['type' => 'list_item', 'content' => [['type' => 'paragraph', 'content' => [
            ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'bold'], ['type' => 'italic']]],
            ['type' => 'hard_break'],
            ['type' => 'text', 'text' => 'evil', 'marks' => [['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']]]],
            ['type' => 'text', 'text' => 'view', 'marks' => [['type' => 'link', 'attrs' => ['href' => '[appointment:linkView]']]]],
        ]]]]]],
    ]];
    $tags = emailContext()->tags();
    $physical = (new TipTapRenderer($tags, TipTapRenderer::TYPE_PHYSICAL))->render($doc);
    expect($physical)->toContain('<h2>Title</h2>')
        ->and($physical)->toContain('<p>Come to 1 Main St</p>')
        ->and($physical)->not->toContain('We call you')
        ->and($physical)->toContain('<li><span><i><strong>bold</strong></i><br/>evilview</span></li>')
        ->and($physical)->not->toContain('javascript');
    expect((new TipTapRenderer($tags, TipTapRenderer::TYPE_PHONE))->render($doc))->toContain('We call you')->not->toContain('Come to');
    expect((new TipTapRenderer($tags, TipTapRenderer::TYPE_ZOOM))->render($doc))->not->toContain('We call you')->not->toContain('Come to');
});

test('templates the renderer cannot represent fail visibly', function (mixed $body) {
    expect(fn() => (new TipTapRenderer(emailContext()->tags(), 0))->render($body))->toThrow(InvalidTemplateException::class);
})->with([
    'legacy non-document body' => [['p' => 'text']],
    'missing body' => [null],
    'image node' => [['type' => 'doc', 'content' => [['type' => 'image', 'attrs' => ['src' => 'x']]]]],
    'todo list' => [['type' => 'doc', 'content' => [['type' => 'todo_list', 'content' => []]]]],
    'unknown mark' => [['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'x', 'marks' => [['type' => 'color']]]]]]]],
    'heading without level' => [['type' => 'doc', 'content' => [['type' => 'heading', 'content' => []]]]],
]);

test('empty subjects and bodies are rejected instead of sending blank email', function () {
    $blank = ['type' => 'doc', 'content' => [['type' => 'paragraph']]];
    expect(fn() => CustomerEmail::compose(['subject' => 'x', 'body' => $blank], emailContext(), emailDisplay()))
        ->toThrow(InvalidTemplateException::class, 'no content');
    expect(fn() => CustomerEmail::compose(['subject' => '  ', 'body' => legacyCanceledBody()], emailContext(), emailDisplay()))
        ->toThrow(InvalidTemplateException::class, 'no subject');
});

test('tags replace only the legacy vocabulary and keep unknown tags literally', function () {
    $tags = emailContext()->tags();
    foreach (EmailTags::SUPPORTED as $tag) {
        expect($tags->value($tag))->not->toBeNull();
    }
    expect($tags->value('appointment:duration'))->toBe('60 min')
        ->and($tags->value('order:summary'))->toBe('')
        ->and($tags->replacePlain('[client:name] [client:unknown] [not a tag]'))->toBe('Ada <script> [client:unknown] [not a tag]')
        ->and(EmailTags::isTag('[appointment:linkNew]'))->toBeTrue()
        ->and(EmailTags::isTag('https://example.invalid'))->toBeFalse();
});

test('organizer emails keep the legacy subjects and detail lines in the calendar timezone', function () {
    $rescheduled = AdminEmail::compose('admin_rescheduled', emailContext(), null, '#123456');
    expect($rescheduled->subject)->toBe('Rescheduled appointment')
        ->and($rescheduled->text)->toContain("Hi Bob,\nA client rescheduled his appointment")
        ->and($rescheduled->text)->toContain('Date: January 1, 2026')
        ->and($rescheduled->text)->toContain('Time: 11:00 - 12:00')
        ->and($rescheduled->text)->toContain("Former appointment\nDate: December 31, 2025")
        ->and($rescheduled->text)->toContain("Client's phone: +32 1")
        ->and($rescheduled->html)->toContain('name: Ada &lt;script&gt;')
        ->and($rescheduled->html)->not->toContain('Confirm appointment');

    $pending = AdminEmail::compose('admin_pending', emailContext(['staffFirstName' => '', 'clientPhone' => '']), 'https://example.invalid/wp-admin/admin.php?page=wappointment-appointments', '#123456');
    expect($pending->subject)->toBe('Pending appointment')
        ->and($pending->text)->toStartWith('Hi,')
        ->and($pending->text)->not->toContain("Client's phone")
        ->and($pending->html)->toContain('Confirm appointment</a>');

    expect(fn() => AdminEmail::compose('customer_confirmed', emailContext(), null, ''))->toThrow(\Wappointment\Jobs\PermanentJobFailure::class);
});

test('the plain-text alternative matches the legacy conversion output', function () {
    // Expected string produced by legacy master `ConvertHtmlToText` for the same input.
    expect(HtmlToText::convert('<h2>Title</h2><p>One &amp; two</p><ul><li>a</li><li>b</li></ul><p><a href="https://x.invalid">link</a></p><p><br/></p><p>End</p>'))
        ->toBe("Title\n\nOne & two\n\n* a\n\n* b\n\nlink ( https://x.invalid )\n\nEnd");
});
