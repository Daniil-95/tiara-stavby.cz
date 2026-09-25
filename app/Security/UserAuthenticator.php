<?php

declare(strict_types=1);

namespace App\Security;

use Nette\Database\Explorer;
use Nette\Http\Request;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class UserAuthenticator implements Authenticator
{
	public function __construct(private Explorer $database, private Request $request) {}

	public function authenticate(string $username, string $password): SimpleIdentity
	{
		$user = $this->database->table('users')->where('email', strtolower(trim($username)))->where('active', 1)->fetch();
		$valid = $user && Passwords::verify($password, $user->password_hash);
		$this->database->table('login_logs')->insert([
			'user_id' => $valid ? $user->id : null,
			'email' => strtolower(trim($username)),
			'success' => (bool) $valid,
			'ip_address' => $this->request->getRemoteAddress() ?: 'unknown',
			'user_agent' => substr((string) $this->request->getHeader('User-Agent'), 0, 255),
		]);
		if (!$valid) throw new AuthenticationException('Invalid credentials.');
		return new SimpleIdentity((int) $user->id, [$user->role], ['name' => $user->name, 'email' => $user->email]);
	}
}
