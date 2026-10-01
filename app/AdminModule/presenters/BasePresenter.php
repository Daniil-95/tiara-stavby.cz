<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use Nette\Application\UI\Form;
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

	protected function createComponentLogoutForm(): Form
	{
		$form = new Form;
		$form->setHtmlAttribute('class', 'admin-logout-form');
		$form->addProtection('Platnost odhlášení vypršela. Obnovte stránku.');
		$form->addSubmit('logout', 'Odhlásit se')->setHtmlAttribute('class', 'admin-logout-button');
		$form->onSuccess[] = function (): void {
			$this->getUser()->logout(true);
			$this->redirect('Login:default');
		};
		return $form;
	}
}
