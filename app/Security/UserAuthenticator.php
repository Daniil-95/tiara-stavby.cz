<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\LoginLogRepository;
use App\Model\UserRepository;
use Nette\Http\Request;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class UserAuthenticator implements Authenticator
{
	public function __construct(private UserRepository $users, private LoginLogRepository $loginLogs, private Request $request, private Passwords $passwords) {}

	public function authenticate(string $username, string $password): SimpleIdentity
	{
		$user = $this->users->findActiveByEmail($username);
		$valid = $user && $this->passwords->verify($password, $user->password_hash);
		$this->loginLogs->record(
			$valid ? (int) $user->id : null,
			$username,
			(bool) $valid,
			$this->request->getRemoteAddress() ?: 'unknown',
			$this->request->getHeader('User-Agent'),
		);
		if (!$valid) throw new AuthenticationException('Invalid credentials.');
		return new SimpleIdentity((int) $user->id, [$user->role], ['name' => $user->name, 'email' => $user->email]);
	}
}
