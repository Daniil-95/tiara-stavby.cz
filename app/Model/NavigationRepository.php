<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class NavigationRepository
{
	public function __construct(private Explorer $database) {}

	public function all(string $lang): array
	{
		return $this->database->table('navigation')->where('lang', $lang)->where('active', 1)->order('sort_order ASC, id ASC')->fetchAll();
	}
}
