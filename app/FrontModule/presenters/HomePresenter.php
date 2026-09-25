<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\ProjectRepository;

final class HomePresenter extends BasePresenter
{
	public function __construct(private ProjectRepository $projects) { parent::__construct(); }

	public function renderDefault(): void
	{
		$this->template->intro = $this->pages->section('home_intro', $this->lang);
		$this->template->about = $this->pages->section('about', $this->lang);
		$this->template->stats = $this->pages->section('stats', $this->lang);
		$this->template->cta = $this->pages->section('cta', $this->lang);
		$this->template->services = $this->services->all($this->lang);
		$this->template->projects = $this->projects->featured($this->lang, 3);
		$this->template->pageTitle = $this->lang === 'en' ? 'Building a better future' : 'Stavíme vaši lepší budoucnost';
	}
}
