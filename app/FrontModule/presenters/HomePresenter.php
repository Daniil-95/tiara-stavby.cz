<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\ProjectRepository;

final class HomePresenter extends BasePresenter
{
	public function __construct(private ProjectRepository $projects) { parent::__construct(); }

	public function renderDefault(): void
	{
		$this->template->intro = $this->pages->section('home_intro', 'cs');
		$this->template->about = $this->pages->section('about', 'cs');
		$this->template->stats = $this->pages->section('stats', 'cs');
		$this->template->cta = $this->pages->section('cta', 'cs');
		$this->template->services = $this->services->all('cs');
		$this->template->projects = $this->projects->featured('cs', 3);
		$this->template->pageTitle = 'Stavíme vaši lepší budoucnost';
	}
}
