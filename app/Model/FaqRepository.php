<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class FaqRepository
{
	public function __construct(private Explorer $database) {}

	public function all(string $lang = 'cs'): array
	{
		return $this->database->table('faqs')
			->where('lang', $lang)
			->where('active', 1)
			->order('sort_order ASC, id ASC')
			->fetchAll();
	}
}
