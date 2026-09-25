<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class ServiceRepository
{
	public function __construct(private Explorer $database) {}

	public function all(string $lang): array
	{
		return $this->database->table('services')->where('lang', $lang)->where('active', 1)->order('sort_order ASC, id ASC')->fetchAll();
	}

	public function findBySlug(string $slug, string $lang): ?array
	{
		$row = $this->database->table('services')->where('slug', $slug)->where('lang', $lang)->where('active', 1)->fetch();
		return $row ? $row->toArray() : null;
	}
}
