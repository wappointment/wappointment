<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;

class DeleteClientRequestData extends RequestData
{
	public function __construct(
		public readonly int $id
	) {}

	public static function schema(): array
	{
		return ApiSchemas::object(['id' => ['type' => 'integer', 'minimum' => 1]]);
	}
}