<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * A row of the unchanged `wappo_jobs` table, decoded in memory.
 *
 * v3 payloads are `{"job": "<registered type>", "version": 1, "params": {...}}`.
 * Legacy payloads store a PHP class name in `job` (for example
 * `\Wappointment\Jobs\AdminEmailNewAppointment`); they are recognised so they can be
 * reported, but never resolved to a class or executed.
 */
final class QueuedJob
{
    public const PAYLOAD_VERSION = 1;

    public function __construct(
        public readonly int $id,
        public readonly string $queue,
        public readonly string $type,
        public readonly array $params,
        public readonly ?int $appointmentId,
        public readonly int $attempts,
        public readonly int $reservedAt,
        public readonly int $availableAt,
        public readonly string $rawPayload,
    ) {}

    /**
     * @throws UnsupportedJobException when the payload is malformed, legacy or of an unknown version.
     */
    public static function fromRow(array $row): self
    {
        $raw = is_string($row['payload'] ?? null) ? $row['payload'] : '';
        try {
            $payload = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new UnsupportedJobException('Job payload is not valid JSON: ' . $error->getMessage());
        }
        if (!is_array($payload) || !is_string($payload['job'] ?? null) || $payload['job'] === '') {
            throw new UnsupportedJobException('Job payload has no job type.');
        }
        if (self::isLegacyClassName($payload['job'])) {
            throw new UnsupportedJobException('Legacy job ' . ltrim($payload['job'], '\\') . ' is not executed by v3.');
        }
        if (($payload['version'] ?? null) !== self::PAYLOAD_VERSION) {
            throw new UnsupportedJobException('Unsupported job payload version.');
        }
        if (!is_array($payload['params'] ?? null)) {
            throw new UnsupportedJobException('Job payload parameters must be an object.');
        }

        return new self(
            (int) $row['id'],
            (string) $row['queue'],
            $payload['job'],
            $payload['params'],
            isset($row['appointment_id']) && $row['appointment_id'] !== null ? (int) $row['appointment_id'] : null,
            (int) $row['attempts'],
            (int) $row['reserved_at'],
            (int) $row['available_at'],
            $raw,
        );
    }

    public static function encode(string $type, array $params): string
    {
        return json_encode(
            ['job' => $type, 'version' => self::PAYLOAD_VERSION, 'params' => (object) $params],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
        );
    }

    public static function isLegacyClassName(string $job): bool
    {
        return str_starts_with(ltrim($job, '\\'), 'Wappointment\\');
    }
}
