<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

final class ProjectRepository
{
	public function __construct(private Explorer $database) {}

	public function featured(string $lang, int $limit = 6): array
	{
		return $this->database->table('projects')->where('lang', $lang)->where('active', 1)->where('featured', 1)->order('sort_order ASC, year DESC')->limit($limit)->fetchAll();
	}

	public function all(string $lang, ?string $category = null): array
	{
		$rows = $this->database->table('projects')->where('lang', $lang)->where('active', 1)->order('featured DESC, sort_order ASC, year DESC');
		if ($category) $rows->where('category', $category);
		return $rows->fetchAll();
	}

	public function find(int $id, string $lang): ?array
	{
		$row = $this->database->table('projects')->where('id', $id)->where('lang', $lang)->where('active', 1)->fetch();
		return $row ? $row->toArray() : null;
	}

	public function gallery(int $projectId): array
	{
		return $this->database->table('project_images')->where('project_id', $projectId)->where('active', 1)->order('sort_order ASC, id ASC')->fetchAll();
	}

	public function adminRows(): array
	{
		return $this->database->table('projects')->order('created_at DESC')->fetchAll();
	}
}
