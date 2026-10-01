<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class LoginLogRepository
{
	public function __construct(private Explorer $database) {}

	public function record(?int $userId, string $email, bool $success, string $ipAddress, ?string $userAgent): void
	{
		$this->database->table('login_logs')->insert([
			'user_id' => $userId,
			'email' => strtolower(trim($email)),
			'success' => $success,
			'ip_address' => $ipAddress,
			'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
		]);
	}

	public function countRecentFailures(string $ipAddress): int
	{
		return $this->database->table('login_logs')
			->where('ip_address', $ipAddress)
			->where('success', 0)
			->where('created_at > ?', new \DateTimeImmutable('-15 minutes'))
			->count('*');
	}
}
