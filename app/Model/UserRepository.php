<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

final class UserRepository
{
	public function __construct(private Explorer $database) {}

	public function findActiveByEmail(string $email): ?ActiveRow
	{
		return $this->database->table('users')->where('email', strtolower(trim($email)))->where('active', 1)->fetch();
	}
}
