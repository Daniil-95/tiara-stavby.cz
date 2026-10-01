<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use Nette\Database\Explorer;

final class DashboardPresenter extends BasePresenter
{
	public function __construct(private Explorer $database) { parent::__construct(); }

	public function renderDefault(): void
	{
		$weekdays = [1 => 'PONDĚLÍ', 'ÚTERÝ', 'STŘEDA', 'ČTVRTEK', 'PÁTEK', 'SOBOTA', 'NEDĚLE'];
		$this->template->weekday = $weekdays[(int) date('N')];
		$this->template->projectCount = $this->database->table('projects')->where('lang', 'cs')->where('active', 1)->count('*');
		$this->template->serviceCount = $this->database->table('services')->where('lang', 'cs')->where('active', 1)->count('*');
		$this->template->newInquiries = $this->database->table('inquiries')->where('status', 'new')->count('*');
		$this->template->latestProjects = $this->database->table('projects')->where('lang', 'cs')->order('created_at DESC')->limit(5)->fetchAll();
		$this->template->latestInquiries = $this->database->table('inquiries')->order('created_at DESC')->limit(5)->fetchAll();
	}

}
