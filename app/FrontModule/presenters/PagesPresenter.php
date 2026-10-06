<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\ProjectRepository;

final class PagesPresenter extends BasePresenter
{
	public function __construct(private ProjectRepository $projects) { parent::__construct(); }

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
	}

	public function renderProjects(?string $category = null): void
	{
		$categories = ['Výstavba', 'Rekonstrukce', 'Modernizace', 'Komerční objekty'];
		if (!in_array($category, $categories, true)) $category = null;
		$this->template->projects = $this->projects->all('cs', $category);
		$this->template->categories = $categories;
		$this->template->selectedCategory = $category;
		$this->template->pageTitle = 'Vybrané realizace';
	}

	public function actionProject(int $id): void
	{
		$project = $this->projects->find($id, 'cs');
		if (!$project) $this->error('Project not found.');
		$this->template->project = $project;
		$this->template->pageTitle = $project['title'];
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
