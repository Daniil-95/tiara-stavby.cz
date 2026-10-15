<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\ProjectRepository;
use App\Model\FaqRepository;

final class PagesPresenter extends BasePresenter
{
	public function __construct(private ProjectRepository $projects, private FaqRepository $faqs) { parent::__construct(); }

	public function renderAbout(): void
	{
		$this->template->section = $this->pages->section('about', 'cs');
		$this->template->pageTitle = 'O společnosti TIARA';
	}

	public function renderServices(): void
	{
		$this->template->services = $this->services->all('cs');
		$this->template->pageTitle = 'Stavební služby';
	}

	public function actionService(string $slug): void
	{
		$service = $this->services->findBySlug($slug, 'cs');
		if (!$service) $this->error('Service not found.');
		$this->template->service = $service;
		$this->template->faqs = $this->faqs->all('cs');
	}

	public function renderProjects(): void
	{
		$this->template->projects = $this->projects->allWithGallery('cs');
		$this->template->pageTitle = 'Vybrané realizace';
	}

	public function renderThanks(): void
	{
		$this->template->pageTitle = 'Děkujeme';
	}

	public function renderError(\Throwable $exception): void
	{
		$this->template->pageTitle = '404';
		$this->template->isDevelopment = $this->getPresenter()->getContext()->getParameters()['production'] ?? false;
	}
}
