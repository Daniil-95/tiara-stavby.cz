<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use App\Model\ImageManager;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;

final class ContentPresenter extends BasePresenter
{
	private const TABLES = ['project' => 'projects', 'service' => 'services', 'inquiry' => 'inquiries', 'navigation' => 'navigation'];
	private const ALIASES = ['projects' => 'project', 'services' => 'service', 'inquiries' => 'inquiry'];
	private const TITLES = ['project' => 'Realizace', 'service' => 'Služby', 'inquiry' => 'Poptávky', 'navigation' => 'Navigace'];
	private const MAX_IMAGE_SIZE = 8 * 1024 * 1024;

	private string $section = 'project';
	private ?array $entry = null;

	public function __construct(private Explorer $database, private ImageManager $images) { parent::__construct(); }

	protected function startup(): void
	{
		parent::startup();
		$section = (string) ($this->getParameter('section') ?? 'project');
		if (isset(self::ALIASES[$section])) {
			$this->redirect('default', ['section' => self::ALIASES[$section], 'operation' => $this->getParameter('operation'), 'id' => $this->getParameter('id')]);
		}
		if (!isset(self::TABLES[$section])) $this->error('Section not found.', 404);
		$this->section = $section;
		$operation = (string) ($this->getParameter('operation') ?? '');
		if (!in_array($operation, ['', 'create', 'edit', 'delete'], true)) $this->error('Operation not found.', 404);
		$id = (int) ($this->getParameter('id') ?? 0);
		if ($id) {
			$row = $this->database->table(self::TABLES[$section])->get($id);
			if (!$row) $this->error('Entry not found.', 404);
			$this->entry = $row->toArray();
		} elseif (in_array($operation, ['edit', 'delete'], true) || ($operation === 'create' && $section === 'inquiry')) {
			$this->error('Entry not found.', 404);
		}
	}

	public function renderDefault(): void
	{
		$this->template->section = $this->section;
		$this->template->operation = (string) ($this->getParameter('operation') ?? '');
		$this->template->entry = $this->entry;
		$this->template->sectionTitle = self::TITLES[$this->section];
		$this->template->pageTitle = self::TITLES[$this->section];
		$this->template->rows = $this->rows();
		$this->template->filteredStatus = $this->getParameter('status');
		$this->template->imageManager = $this->images;
		$this->template->currentImage = $this->entry ? ($this->entry['main_image'] ?? $this->entry['image_path'] ?? null) : null;
	}

	protected function createComponentEntryForm(): Form
	{
		$form = new Form;
		$form->setAction($this->getHttpRequest()->getUrl()->getPath());
		$form->setHtmlAttribute('class', 'admin-entry-form');
		$this->addFields($form);
		$form->addProtection('Formulář vypršel. Obnovte stránku a zkuste to znovu.');
		$form->addSubmit('save', 'Uložit změny')->setHtmlAttribute('class', 'admin-button');
		if ($this->entry && $this->getParameter('operation') === 'edit') $form->setDefaults($this->entry);
		$form->onSuccess[] = [$this, 'saveEntry'];
		return $form;
	}

	public function saveEntry(Form $form, \stdClass $values): void
	{
		$editing = $this->getParameter('operation') === 'edit' && $this->entry !== null;
		$table = self::TABLES[$this->section];
		$newImage = null;
		try {
			$data = get_object_vars($values);
			$upload = $data['image'] ?? null;
			unset($data['image'], $data['save']);

			if ($this->section === 'inquiry') {
				$this->database->table($table)->where('id', $this->entry['id'])->update(['status' => $data['status']]);
				$this->flashMessage('Stav poptávky byl uložen.', 'success');
				$this->redirect('default', ['section' => $this->section]);
			}

			foreach (['title', 'slug', 'location', 'icon', 'url'] as $textField) {
				if (isset($data[$textField])) $data[$textField] = trim((string) $data[$textField]);
			}
			if (array_key_exists('slug', $data)) {
				$data['slug'] = Strings::webalize($data['slug'] !== '' ? $data['slug'] : $data['title']);
				if ($data['slug'] === '') {
					$form['slug']->addError('Vyplňte URL slug nebo název, ze kterého se vytvoří.');
				} elseif ($this->exists($table, 'slug', $data['slug'], $editing)) {
					$form['slug']->addError('Tento URL slug už existuje. Zvolte jiný.');
				}
			}
			if ($this->section === 'navigation') {
				if (!$this->isSafeLink($data['url'])) {
					$form['url']->addError('Odkaz musí být interní cesta (např. /sluzby) nebo adresa HTTP/HTTPS.');
				} elseif ($this->exists($table, 'url', $data['url'], $editing)) {
					$form['url']->addError('Položka s tímto odkazem už v navigaci je.');
				}
			}
			if ($form->hasErrors()) return;

			$data['lang'] = 'cs';
			foreach (['active', 'featured'] as $checkbox) {
				if (array_key_exists($checkbox, $data)) $data[$checkbox] = (int) $data[$checkbox];
			}
			if ($upload instanceof FileUpload && $upload->hasFile()) {
				$newImage = $this->images->saveProjectImage($upload);
				$data[$this->section === 'project' ? 'main_image' : 'image_path'] = $newImage;
			}

			if ($editing) {
				$this->database->table($table)->where('id', $this->entry['id'])->update($data);
				$oldImage = $this->entry['main_image'] ?? $this->entry['image_path'] ?? null;
				if ($newImage && $oldImage && $oldImage !== $newImage) $this->images->deleteProjectImage($oldImage);
			} else {
				$this->database->table($table)->insert($data);
			}
			$this->flashMessage($editing ? 'Změny byly uloženy.' : 'Záznam byl přidán.', 'success');
			$this->redirect('default', ['section' => $this->section]);
		} catch (\Nette\Application\AbortException $e) {
			throw $e;
		} catch (\RuntimeException $e) {
			if ($newImage) $this->images->deleteProjectImage($newImage);
			$form->addError($e->getMessage());
		} catch (\Throwable $e) {
			if ($newImage) $this->images->deleteProjectImage($newImage);
			error_log('Admin save failed: ' . $e->getMessage());
			$form->addError('Změny se nepodařilo uložit. Zkontrolujte vyplněná pole a zkuste to znovu.');
		}
	}

	protected function createComponentDeleteForm(): Form
	{
		$form = new Form;
		$form->setAction($this->getHttpRequest()->getUrl()->getPath());
		$form->addProtection('Potvrzení vypršelo. Obnovte stránku a zkuste to znovu.');
		$form->addSubmit('delete', 'Ano, smazat')->setHtmlAttribute('class', 'admin-button admin-button--danger');
		$form->onSuccess[] = function (): void {
			if (!$this->entry) $this->error('Entry not found.', 404);
			$id = (int) $this->entry['id'];
			if ($this->section === 'project') {
				foreach ($this->database->table('project_images')->where('project_id', $id)->fetchAll() as $image) $this->images->deleteProjectImage($image->image_path);
			}
			$this->images->deleteProjectImage($this->entry['main_image'] ?? $this->entry['image_path'] ?? null);
			$this->database->table(self::TABLES[$this->section])->where('id', $id)->delete();
			$this->flashMessage('Záznam byl odstraněn.', 'success');
			$this->redirect('default', ['section' => $this->section]);
		};
		return $form;
	}

	private function addFields(Form $form): void
	{
		$imageRule = static fn ($upload) => $upload->addRule($form::MaxFileSize, 'Maximální velikost fotografie je 8 MB.', self::MAX_IMAGE_SIZE);
		$order = static fn () => $form->addInteger('sort_order', 'Pořadí (menší číslo je výše)')->setRequired('Zadejte pořadí, např. 0.')->setDefaultValue(0);
		switch ($this->section) {
			case 'project':
				$form->addText('title', 'Název')->setRequired('Zadejte název.')->addRule($form::MaxLength, 'Název je příliš dlouhý.', 220);
				$form->addText('slug', 'URL slug (nepovinné, vytvoří se z názvu)')->addRule($form::MaxLength, 'URL slug je příliš dlouhý.', 220);
				$form->addSelect('category', 'Kategorie', array_combine($c = ['Výstavba', 'Rekonstrukce', 'Modernizace', 'Komerční objekty'], $c))->setPrompt('Vyberte kategorii')->setRequired('Vyberte kategorii.');
				$form->addText('location', 'Lokalita')->setRequired('Zadejte lokalitu.')->addRule($form::MaxLength, 'Lokalita je příliš dlouhá.', 180);
				$form->addInteger('year', 'Rok')->setRequired('Zadejte rok.')->addRule($form::Range, 'Zadejte rok mezi 1900 a 2100.', [1900, 2100]);
				$form->addTextArea('short_description', 'Krátký popis')->setRequired('Zadejte krátký popis.')->addRule($form::MaxLength, 'Krátký popis může mít nejvýše 500 znaků.', 500);
				$form->addTextArea('description', 'Popis')->setRequired('Zadejte popis.');
				$imageRule($form->addUpload('image', 'Hlavní fotografie'));
				$order();
				$form->addCheckbox('active', 'Zobrazit na webu')->setDefaultValue(true);
				$form->addCheckbox('featured', 'Zobrazit mezi doporučenými na úvodní stránce');
				break;
			case 'service':
				$form->addText('title', 'Název')->setRequired('Zadejte název.')->addRule($form::MaxLength, 'Název je příliš dlouhý.', 180);
				$form->addText('slug', 'URL slug (nepovinné, vytvoří se z názvu)')->addRule($form::MaxLength, 'URL slug je příliš dlouhý.', 190);
				$form->addTextArea('short_description', 'Krátký popis')->setRequired('Zadejte krátký popis.')->addRule($form::MaxLength, 'Krátký popis může mít nejvýše 500 znaků.', 500);
				$form->addTextArea('content', 'Obsah')->setRequired('Zadejte obsah.');
				$imageRule($form->addUpload('image', 'Fotografie'));
				$form->addText('icon', 'Ikona (Font Awesome)')->setRequired('Zadejte ikonu.')->addRule($form::Pattern, 'Zadejte třídy Font Awesome, např. fa-solid fa-house.', '[a-z0-9 \\-]+')->addRule($form::MaxLength, 'Ikona je příliš dlouhá.', 80)->setDefaultValue('fa-solid fa-house');
				$order();
				$form->addCheckbox('active', 'Zobrazit na webu')->setDefaultValue(true);
				break;
			case 'navigation':
				$form->addText('title', 'Název')->setRequired('Zadejte název.')->addRule($form::MaxLength, 'Název je příliš dlouhý.', 120);
				$form->addText('url', 'Odkaz (např. /sluzby)')->setRequired('Zadejte odkaz.')->addRule($form::MaxLength, 'Odkaz je příliš dlouhý.', 255);
				$order();
				$form->addCheckbox('active', 'Zobrazit v menu')->setDefaultValue(true);
				break;
			case 'inquiry':
				$form->addSelect('status', 'Stav', ['new' => 'Nová', 'contacted' => 'Kontaktováno', 'closed' => 'Uzavřeno'])->setRequired();
				break;
		}
	}

	private function exists(string $table, string $column, string $value, bool $editing): bool
	{
		$selection = $this->database->table($table)->where('lang', 'cs')->where($column, $value);
		if ($editing) $selection->where('id != ?', $this->entry['id']);
		return $selection->count('*') > 0;
	}

	private function isSafeLink(string $url): bool
	{
		if (str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\') && !preg_match('/[\r\n]/', $url)) return true;
		return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false;
	}

	private function rows(): array
	{
		$operation = (string) ($this->getParameter('operation') ?? '');
		if ($operation !== '') return [];
		$selection = $this->database->table(self::TABLES[$this->section])->where('lang', 'cs');
		if ($this->section === 'inquiry') {
			$selection->order('created_at DESC');
			$status = $this->getParameter('status');
			if (in_array($status, ['new', 'contacted', 'closed'], true)) $selection->where('status', $status);
		} elseif ($this->section === 'navigation' || $this->section === 'service') {
			$selection->order('sort_order ASC, id ASC');
		} else {
			$selection->order('sort_order ASC, id DESC');
		}
		return $selection->limit(250)->fetchAll();
	}
}
