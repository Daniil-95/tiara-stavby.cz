<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	protected function startup(): void
	{
		parent::startup();
		if (!$this->getUser()->isLoggedIn() || !$this->getUser()->isInRole('admin')) {
			$this->redirect('Login:default');
		}
	}

	protected function beforeRender(): void
	{
		parent::beforeRender();
		$this->template->adminUser = $this->getUser()->getIdentity()?->name ?? 'Admin';
	}
}
