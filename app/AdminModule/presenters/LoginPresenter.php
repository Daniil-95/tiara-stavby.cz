<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

final class LoginPresenter extends Presenter
{
	protected function startup(): void
	{
		parent::startup();
		if ($this->getUser()->isLoggedIn() && $this->getUser()->isInRole('admin')) $this->redirect('Dashboard:default');
	}

	protected function createComponentLoginForm(): Form
	{
		$form = new Form;
		$form->addEmail('email', 'E-mail')->setRequired('Zadejte e-mail.');
		$form->addPassword('password', 'Heslo')->setRequired('Zadejte heslo.');
		$form->addProtection('Formulář vypršel. Obnovte stránku a zkuste to znovu.');
		$form->addSubmit('send', 'Přihlásit se')->setHtmlAttribute('class', 'admin-button');
		$form->onSuccess[] = function (Form $form, \stdClass $values): void {
			try {
				$this->getUser()->login($values->email, $values->password);
				if (!$this->getUser()->isInRole('admin')) {
					$this->getUser()->logout(true);
					$form->addError('Účet nemá oprávnění administrátora.');
					return;
				}
				$this->redirect('Dashboard:default');
			} catch (\Nette\Security\AuthenticationException) {
				$form->addError('Nesprávný e-mail nebo heslo.');
			}
		};
		return $form;
	}
}
