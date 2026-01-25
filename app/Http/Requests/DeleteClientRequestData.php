<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\System\Data;

class DeleteClientRequestData extends Data
{
	public function __construct(
		public readonly int $id
	) {}

	public static function fromWpRequest(\WP_REST_Request $request): static
	{
		return static::from([
			'id' => (int) $request->get_param('id'),
		]);
	}
}