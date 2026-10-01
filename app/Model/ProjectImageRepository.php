<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;

final class ProjectImageRepository
{
	public function __construct(private Explorer $database) {}

	public function activeForProject(int $projectId): array
	{
		return $this->database->table('project_images')->where('project_id', $projectId)->where('active', 1)->order('sort_order ASC, id ASC')->fetchAll();
	}
}
