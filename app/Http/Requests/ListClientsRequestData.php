<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\ApiSchemas;
use Wappointment\Http\ContractValidator;

class ListClientsRequestData extends RequestData
{
	public function __construct(
		public readonly int $page = 1,
		public readonly int $perPage = 10,
		public readonly string $search = ''
	) {}

	public static function schema(): array
	{
		return ApiSchemas::paginationRequest(true);
	}

	public static function from(array $data): static
	{
		$data = ContractValidator::validate($data, static::schema(), 'request', true);
		return new static($data['page_num'] ?? 1, $data['per_page'] ?? 10, $data['search'] ?? '');
	}
}