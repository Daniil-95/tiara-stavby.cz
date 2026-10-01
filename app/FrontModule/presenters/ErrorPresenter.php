<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use Throwable;

final class ErrorPresenter extends BasePresenter
{
	public function renderDefault(?Throwable $exception = null): void
	{
		$this->getHttpResponse()->setCode($exception === null || $exception instanceof \Nette\Application\BadRequestException ? 404 : 500);
		$this->template->pageTitle = '404';
	}
}
