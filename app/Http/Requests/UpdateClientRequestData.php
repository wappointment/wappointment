<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class UpdateClientRequestData extends RequestData
{
	public function __construct(
		public readonly int $id,
		public readonly ?string $name = null,
		public readonly ?string $email = null,
		public readonly ?array $options = null,
	) {}

	public static function schema(): array
	{
		return ApiSchemas::object([
			'id' => ['type' => 'integer', 'minimum' => 1],
			'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
			'email' => ['type' => 'string', 'format' => 'email', 'maxLength' => 191],
			'options' => ApiSchemas::object([], [], true),
		], ['id']);
	}

}