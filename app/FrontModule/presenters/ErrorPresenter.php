<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use Throwable;

final class ErrorPresenter extends BasePresenter
{
	public function renderDefault(Throwable $exception): void
	{
		$this->getHttpResponse()->setCode(404);
		$this->template->exception = $exception;
		$this->template->pageTitle = '404';
	}
}
