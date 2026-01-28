<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\System\Data;

class ListClientsRequestData extends Data
{
	public function __construct(
		public readonly int $page = 1,
		public readonly int $perPage = 10,
		public readonly string $search = ''
	) {}

	public static function fromWpRequest(\WP_REST_Request $request): static
	{
		$page = (int) $request->get_param('page_num') ?: 1;
		$perPage = (int) $request->get_param('per_page') ?: 10;
		$search = sanitize_text_field((string) ($request->get_param('search') ?? ''));

		return static::from([
			'page' => $page,
			'perPage' => $perPage,
			'search' => $search,
		]);
	}
}