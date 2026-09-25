<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class SeoRepository
{
	public function __construct(private Explorer $database) {}

	public function forPath(string $path, string $lang): ?array
	{
		$row = $this->database->table('seo_metadata')->where('page_path', $path)->where('lang', $lang)->fetch();
		return $row ? $row->toArray() : null;
	}
}
