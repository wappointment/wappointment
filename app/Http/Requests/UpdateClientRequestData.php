<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\System\Data;

class UpdateClientRequestData extends Data
{
	public function __construct(
		public readonly int $id,
		public readonly ?string $name = null,
		public readonly ?string $email = null,
		public readonly ?array $options = null,
	) {}

	public static function fromWpRequest(\WP_REST_Request $request): static
	{
		$payload = $request->get_json_params() ?? [];
		return static::from([
			'id' => (int) $request->get_param('id'),
			'name' => $payload['name'] ?? null,
			'email' => isset($payload['email']) ? sanitize_email($payload['email']) : null,
			'options' => $payload['options'] ?? null,
		]);
	}

	public function updatePayload(): array
	{
		$data = [];
		if ($this->name !== null) {
			$data['name'] = $this->name;
		}
		if ($this->email !== null) {
			$data['email'] = $this->email;
		}
		if ($this->options !== null) {
			$data['options'] = json_encode($this->options);
		}
		$data['updated_at'] = current_time('mysql');
		return $data;
	}
}