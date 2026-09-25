<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class SettingRepository
{
	public function __construct(private Explorer $database) {}

	public function all(): array
	{
		$result = [];
		foreach ($this->database->table('settings')->fetchAll() as $row) $result[$row->setting_key] = $row->setting_value;
		return $result;
	}

	public function get(string $key, string $fallback = ''): string
	{
		return (string) ($this->database->table('settings')->where('setting_key', $key)->fetchField('setting_value') ?? $fallback);
	}
}
