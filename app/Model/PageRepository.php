<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class PageRepository
{
	public function __construct(private Explorer $database) {}

	public function section(string $key, string $lang): ?array
	{
		$row = $this->database->table('page_sections')->where('section_key', $key)->where('lang', $lang)->where('active', 1)->fetch();
		return $row ? $row->toArray() : null;
	}

	public function sections(string $lang): array
	{
		return $this->database->table('page_sections')->where('lang', $lang)->order('sort_order ASC, id ASC')->fetchAll();
	}
}
