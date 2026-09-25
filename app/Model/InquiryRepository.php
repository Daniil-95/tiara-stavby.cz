<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Nette\Database\Explorer;

final class InquiryRepository
{
	public function __construct(private Explorer $database) {}

	public function create(array $data): int
	{
		return (int) $this->database->table('inquiries')->insert($data)->id;
	}

	public function countRecent(string $ip): int
	{
		return $this->database->table('inquiries')->where('ip_address', $ip)->where('created_at >', new DateTimeImmutable('-1 hour'))->count('*');
	}
}
