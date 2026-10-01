<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use App\Model\ImageManager;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette\Http\FileUpload;

final class ContentPresenter extends BasePresenter
{
	private const TABLES = [
		'projects' => 'projects', 'services' => 'services', 'pages' => 'page_sections',
		'inquiries' => 'inquiries', 'settings' => 'settings', 'navigation' => 'navigation',
		'seo' => 'seo_metadata', 'gallery' => 'project_images',
	];
	private string $section = 'projects';
	private ?array $entry = null;

	public function __construct(private Explorer $database, private ImageManager $images) { parent::__construct(); }

	protected function startup(): void
	{
		parent::startup();
		$routeSection = (string) ($this->getParameter('section') ?? 'projects');
		$this->section = ['project' => 'projects', 'service' => 'services', 'page' => 'pages', 'inquiry' => 'inquiries', 'setting' => 'settings', 'navigation' => 'navigation'][$routeSection] ?? $routeSection;
		if (!isset(self::TABLES[$this->section])) $this->error('Section not found.', 404);
		$id = (int) ($this->getParameter('id') ?? 0);
		$row = $id ? $this->database->table(self::TABLES[$this->section])->get($id) : null;
		$this->entry = $row ? $row->toArray() : null;
		if ($id && !$this->entry) $this->error('Entry not found.', 404);
	}

	public function renderDefault(): void
	{
		$operation = (string) ($this->getParameter('operation') ?? '');
		$this->template->section = $this->section;
		$this->template->operation = $operation;
		$this->template->entry = $this->entry;
		$this->template->sectionTitle = $this->titleFor($this->section);
		$this->template->rows = $this->rows();
		$this->template->projectOptions = $this->projectOptions();
		$this->template->galleryProjectId = (int) ($this->getParameter('project_id') ?? 0);
		$this->template->filteredStatus = $this->getParameter('status');
		$this->template->adminLang = in_array($this->getParameter('lang'), ['cs', 'en'], true) ? $this->getParameter('lang') : 'cs';
	}

	protected function createComponentEntryForm(): Form
	{
		$form = new Form;
		$form->setAction($this->currentFormAction());
		$this->addFields($form);
		$form->addProtection('Formulář vypršel. Obnovte stránku.');
		$form->addSubmit('save', 'Uložit změny')->setHtmlAttribute('class', 'admin-button');
		$mode = (string) ($this->getParameter('operation') ?? 'create');
		if ($mode === 'edit' && $this->entry) $form->setDefaults($this->entry);
		$form->onSuccess[] = function (Form $form, \stdClass $values): void {
			try {
				$data = get_object_vars($values);
				$upload = $data['image'] ?? null;
				unset($data['image'], $data['save']);
				if ($upload instanceof FileUpload && $upload->hasFile()) {
					$data[$this->section === 'projects' ? 'main_image' : 'image_path'] = $this->images->saveProjectImage($upload);
				}
				foreach (['active', 'featured', 'consent'] as $checkbox) if (isset($data[$checkbox])) $data[$checkbox] = (int) $data[$checkbox];
				if ($this->section === 'inquiries') {
					$this->database->table('inquiries')->where('id', (int) $this->getParameter('id'))->update(['status' => $data['status']]);
				} elseif ($this->section === 'gallery') {
					$imagePath = $data['image_path'] ?? ($this->entry['image_path'] ?? '');
					unset($data['image_path']);
					$project = $this->database->table('projects')->get((int) $data['project_id']);
					$galleryData = [
						'project_id' => (int) $data['project_id'], 'lang' => $project->lang, 'image_path' => $imagePath,
						'title' => $data['title'] ?: null, 'alt_text' => $data['alt_text'], 'sort_order' => (int) $data['sort_order'],
					];
					if ($this->getParameter('operation') === 'edit') $this->database->table('project_images')->where('id', (int) $this->getParameter('id'))->update($galleryData);
					else $this->database->table('project_images')->insert($galleryData);
				} elseif ($this->section === 'settings') {
					if ($this->getParameter('operation') === 'edit') $this->database->table('settings')->where('id', (int) $this->getParameter('id'))->update($data);
					else $this->database->table('settings')->insert($data);
				} else {
					if ($this->section === 'navigation') {
						$url = trim((string) $data['url']);
						$scheme = parse_url($url, PHP_URL_SCHEME);
						$internal = str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\') && !preg_match('/[\r\n]/', $url);
						$external = in_array(strtolower((string) $scheme), ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false;
						if (!$internal && !$external) {
							$form->addError('Odkaz musí být interní cesta nebo bezpečná adresa HTTP/HTTPS.');
							return;
						}
						$data['url'] = $url;
					}
					$mode = (string) ($this->getParameter('operation') ?? 'create');
					if ($mode === 'edit') $this->database->table(self::TABLES[$this->section])->where('id', (int) $this->getParameter('id'))->update($data);
					else $this->database->table(self::TABLES[$this->section])->insert($data);
				}
				$this->flashMessage('Změny byly uloženy.', 'success');
				$this->redirect('default', ['section' => $this->section]);
			} catch (\Nette\Application\AbortException $e) {
				throw $e;
			} catch (\Throwable $e) {
				error_log('Admin save failed: ' . $e->getMessage());
				$form->addError('Změny se nepodařilo uložit. Zkontrolujte vyplněná pole a zkuste to znovu.');
			}
		};
		return $form;
	}

	protected function createComponentDeleteForm(): Form
	{
		$form = new Form;
		$form->setAction($this->currentFormAction());
		$form->addProtection('Potvrzení vypršelo. Obnovte stránku.');
		$form->addSubmit('delete', 'Ano, smazat')->setHtmlAttribute('class', 'admin-button admin-button--danger');
		$form->onSuccess[] = function (): void {
			$id = (int) $this->getParameter('id');
			if ($this->section === 'settings') {
				$this->database->table('settings')->where('id', $id)->delete();
			} elseif ($this->section === 'projects') {
				foreach ($this->database->table('project_images')->where('project_id', $id)->fetchAll() as $image) $this->images->deleteProjectImage($image->image_path);
				$project = $this->database->table('projects')->get($id);
				if ($project) $this->images->deleteProjectImage($project->main_image);
				$this->database->table('projects')->where('id', $id)->delete();
			} elseif ($this->section === 'gallery') {
				$image = $this->database->table('project_images')->get($id);
				if ($image) $this->images->deleteProjectImage($image->image_path);
				$this->database->table('project_images')->where('id', $id)->delete();
			} else {
				$this->database->table(self::TABLES[$this->section])->where('id', $id)->delete();
			}
			$this->flashMessage('Záznam byl odstraněn.', 'success');
			$this->redirect('default', ['section' => $this->section]);
		};
		return $form;
	}

	protected function createComponentGalleryOrderForm(): Form
	{
		$form = new Form;
		$form->setAction($this->currentFormAction());
		$form->addHidden('order')->setDefaultValue(implode(',', array_map(static fn($row) => (string) $row->id, $this->rows())));
		$form->addProtection('Pořadí fotografií vypršelo. Obnovte stránku.');
		$form->addSubmit('save', 'Uložit pořadí')->setHtmlAttribute('class', 'admin-button');
		$form->onSuccess[] = function (Form $form, \stdClass $values): void {
			$projectId = (int) ($this->getParameter('project_id') ?? 0);
			$ids = array_values(array_filter(array_map('intval', explode(',', (string) $values->order))));
			$allowed = array_map(static fn($row) => (int) $row->id, $this->database->table('project_images')->where('project_id', $projectId)->fetchAll());
			$sortedIds = $ids; $sortedAllowed = $allowed; sort($sortedIds); sort($sortedAllowed);
			if (!$projectId || !$ids || count($ids) !== count(array_unique($ids)) || $sortedIds !== $sortedAllowed) {
				$form->addError('Pořadí fotografií není platné. Obnovte stránku a zkuste to znovu.');
				return;
			}
			foreach ($ids as $order => $imageId) $this->database->table('project_images')->where('id', $imageId)->where('project_id', $projectId)->update(['sort_order' => $order + 1]);
			$this->flashMessage('Pořadí fotografií bylo uloženo.', 'success');
			$this->redirect('default', ['section' => 'gallery', 'project_id' => $projectId]);
		};
		return $form;
	}

	private function addFields(Form $form): void
	{
		switch ($this->section) {
			case 'projects':
				$form->addSelect('lang', 'Jazyk', ['cs' => 'Čeština', 'en' => 'English'])->setRequired();
				$form->addText('title', 'Název')->setRequired(); $form->addText('slug', 'URL slug')->setRequired();
				$form->addSelect('category', 'Kategorie', ['Výstavba' => 'Výstavba', 'Rekonstrukce' => 'Rekonstrukce', 'Modernizace' => 'Modernizace', 'Komerční objekty' => 'Komerční objekty', 'Construction' => 'Construction', 'Renovation' => 'Renovation', 'Modernisation' => 'Modernisation', 'Commercial' => 'Commercial'])->setRequired();
				$form->addText('location', 'Lokalita')->setRequired(); $form->addText('year', 'Rok')->setRequired();
				$form->addTextArea('short_description', 'Krátký popis')->setRequired(); $form->addTextArea('description', 'Popis')->setRequired();
				$form->addUpload('image', 'Hlavní fotografie')->addRule($form::MaxFileSize, 'Maximální velikost je 8 MB.', 8 * 1024 * 1024);
				$form->addCheckbox('active', 'Publikovat')->setDefaultValue(true); $form->addCheckbox('featured', 'Doporučený projekt'); $form->addText('sort_order', 'Pořadí')->setDefaultValue(0);
				break;
			case 'services':
				$form->addSelect('lang', 'Jazyk', ['cs' => 'Čeština', 'en' => 'English'])->setRequired();
				$form->addText('title', 'Název')->setRequired(); $form->addText('slug', 'URL slug')->setRequired(); $form->addTextArea('short_description', 'Krátký popis')->setRequired();
				$form->addTextArea('content', 'Obsah')->setRequired(); $form->addUpload('image', 'Fotografie')->addRule($form::MaxFileSize, 'Maximální velikost je 8 MB.', 8 * 1024 * 1024);
				$form->addText('icon', 'Font Awesome class')->setDefaultValue('fa-solid fa-house'); $form->addCheckbox('active', 'Publikovat')->setDefaultValue(true); $form->addText('sort_order', 'Pořadí')->setDefaultValue(0);
				break;
			case 'pages':
				$form->addText('section_key', 'Klíč sekce')->setRequired(); $form->addSelect('lang', 'Jazyk', ['cs' => 'Čeština', 'en' => 'English'])->setRequired();
				$form->addText('title', 'Nadpis')->setRequired(); $form->addText('subtitle', 'Podnadpis'); $form->addTextArea('content', 'Obsah')->setRequired(); $form->addText('image_path', 'Cesta k obrázku'); $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true); $form->addText('sort_order', 'Pořadí')->setDefaultValue(0);
				break;
			case 'inquiries':
				$form->addSelect('status', 'Stav', ['new' => 'Nová', 'contacted' => 'Kontaktováno', 'closed' => 'Uzavřeno'])->setRequired();
				break;
			case 'settings':
				$form->addText('setting_key', 'Klíč')->setRequired(); $form->addTextArea('setting_value', 'Hodnota')->setRequired(); $form->addText('setting_group', 'Skupina')->setRequired();
				break;
			case 'navigation':
				$form->addSelect('lang', 'Jazyk', ['cs' => 'Čeština', 'en' => 'English'])->setRequired(); $form->addText('title', 'Název')->setRequired(); $form->addText('url', 'URL')->setRequired()->addRule($form::MaxLength, null, 255); $form->addCheckbox('active', 'Aktivní')->setDefaultValue(true); $form->addText('sort_order', 'Pořadí')->setDefaultValue(0);
				break;
			case 'seo':
				$form->addText('page_path', 'Cesta stránky')->setRequired(); $form->addSelect('lang', 'Jazyk', ['cs' => 'Čeština', 'en' => 'English'])->setRequired(); $form->addText('meta_title', 'Meta title'); $form->addTextArea('meta_description', 'Meta description'); $form->addText('og_title', 'OG title'); $form->addTextArea('og_description', 'OG description'); $form->addText('og_image', 'OG image URL'); $form->addText('canonical_url', 'Canonical URL'); $form->addText('robots', 'Robots')->setDefaultValue('index,follow');
				break;
			case 'gallery':
				$form->addSelect('project_id', 'Projekt', $this->projectOptions())->setRequired(); $form->addUpload('image', 'Fotografie')->addRule($form::MaxFileSize, 'Maximální velikost je 8 MB.', 8 * 1024 * 1024); $form->addText('title', 'Název'); $form->addText('alt_text', 'Alternativní text')->setRequired(); $form->addText('sort_order', 'Pořadí')->setDefaultValue(0);
				break;
		}
		if ($this->section === 'gallery' && $this->getParameter('operation') !== 'edit') $form['image']->setRequired('Vyberte fotografii.');
	}

	private function rows(): array
	{
		if ($this->section === 'inquiries') {
			$selection = $this->database->table('inquiries')->order('created_at DESC');
			$status = $this->getParameter('status');
			if (in_array($status, ['new', 'contacted', 'closed'], true)) $selection->where('status', $status);
			return $selection->limit(250)->fetchAll();
		}
		if ($this->section === 'gallery') {
			$selection = $this->database->table('project_images')->order('sort_order ASC, id ASC');
			$projectId = (int) ($this->getParameter('project_id') ?? 0);
			if ($projectId) $selection->where('project_id', $projectId);
			return $selection->limit(250)->fetchAll();
		}
		$selection = $this->database->table(self::TABLES[$this->section])->order('id DESC');
		$lang = $this->getParameter('lang');
		if (in_array($this->section, ['projects', 'services', 'pages', 'navigation', 'seo'], true) && in_array($lang, ['cs', 'en'], true)) $selection->where('lang', $lang);
		return $selection->limit(250)->fetchAll();
	}

	private function projectOptions(): array
	{
		$options = [];
		foreach ($this->database->table('projects')->order('title ASC')->fetchAll() as $project) $options[$project->id] = $project->title . ' (' . strtoupper($project->lang) . ')';
		return $options;
	}

	private function titleFor(string $section): string
	{
		return ['projects' => 'Realizované projekty', 'services' => 'Služby', 'pages' => 'Stránky a sekce', 'inquiries' => 'Poptávky', 'settings' => 'Nastavení', 'navigation' => 'Navigace', 'seo' => 'SEO', 'gallery' => 'Galerie projektu'][$section] ?? 'Obsah';
	}

	private function currentFormAction(): string
	{
		$url = $this->getHttpRequest()->getUrl();
		return $url->getPath() . ($url->getQuery() !== '' ? '?' . $url->getQuery() : '');
	}
}
