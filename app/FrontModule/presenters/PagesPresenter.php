<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\ProjectRepository;

final class PagesPresenter extends BasePresenter
{
	public function __construct(private ProjectRepository $projects) { parent::__construct(); }

	public function renderAbout(): void
	{
		$this->template->section = $this->pages->section('about', $this->lang);
		$this->template->pageTitle = $this->lang === 'en' ? 'About TIARA' : 'O společnosti TIARA';
	}

	public function renderServices(): void
	{
		$this->template->services = $this->services->all($this->lang);
		$this->template->pageTitle = $this->lang === 'en' ? 'Construction services' : 'Stavební služby';
	}

	public function actionService(string $slug): void
	{
		$service = $this->services->findBySlug($slug, $this->lang);
		if (!$service) $this->error('Service not found.');
		$this->template->service = $service;
	}

	public function renderProjects(): void
	{
		$this->template->projects = $this->projects->all($this->lang);
		$this->template->pageTitle = $this->lang === 'en' ? 'Selected projects' : 'Vybrané realizace';
	}

	public function actionProject(int $id): void
	{
		$project = $this->projects->find($id, $this->lang);
		if (!$project) $this->error('Project not found.');
		$this->template->project = $project;
		$this->template->images = $this->projects->gallery($id);
		$this->template->pageTitle = $project['title'];
	}

	public function renderGallery(): void
	{
		$this->template->projects = $this->projects->all($this->lang);
		$this->template->pageTitle = $this->lang === 'en' ? 'Our references' : 'Naše reference';
	}

	public function renderContact(): void
	{
		$this->template->pageTitle = $this->lang === 'en' ? 'Contact TIARA' : 'Kontaktujte TIARA';
	}

	public function renderThanks(): void
	{
		$this->template->pageTitle = $this->lang === 'en' ? 'Thank you' : 'Děkujeme';
	}

	public function renderError(\Throwable $exception): void
	{
		$this->template->pageTitle = '404';
		$this->template->isDevelopment = $this->getPresenter()->getContext()->getParameters()['production'] ?? false;
	}
}
