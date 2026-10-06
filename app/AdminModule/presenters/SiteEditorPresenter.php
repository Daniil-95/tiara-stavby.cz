<?php

declare(strict_types=1);

namespace App\AdminModule\presenters;

use App\Model\ImageManager;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette\Http\FileUpload;

final class SiteEditorPresenter extends BasePresenter
{
	private const PAGES = [
		'home' => 'Úvodní stránka',
		'about' => 'O nás',
		'contact' => 'Kontakt',
		'seo' => 'SEO',
		'settings' => 'Nastavení webu',
	];
	private const SEO_PAGES = [
		'/' => 'Úvodní stránka', '/o-nas' => 'O nás', '/sluzby' => 'Služby',
		'/realizace' => 'Realizace',
	];

	private string $page = 'home';

	public function __construct(private Explorer $database, private ImageManager $images)
	{
		parent::__construct();
	}

	protected function startup(): void
	{
		parent::startup();
		$page = (string) ($this->getParameter('page') ?? 'home');
		if (!isset(self::PAGES[$page])) $this->error('Page not found.', 404);
		$this->page = $page;
	}

	public function renderDefault(): void
	{
		$this->template->pageTitle = self::PAGES[$this->page];
		$this->template->editorPage = $this->page;
		$this->template->editorPages = self::PAGES;
		$this->template->seoPages = self::SEO_PAGES;
		$this->template->editorData = $this->loadData();
		$this->template->imageManager = $this->images;
	}

	protected function createComponentPageForm(): Form
	{
		$form = new Form;
		$form->setAction($this->getHttpRequest()->getUrl()->getPath());
		$form->setHtmlAttribute('class', 'site-editor-form');
		$this->addPageFields($form);
		$form->addProtection('Formulář vypršel. Obnovte stránku a zkuste to znovu.');
		$form->addSubmit('save', 'Uložit změny')->setHtmlAttribute('class', 'admin-button');
		$form->setDefaults($this->loadData());
		$form->onSuccess[] = function (Form $form, \stdClass $values): void {
			try {
				$data = get_object_vars($values);
				if ($this->page === 'home') {
					$this->saveHome($data);
				}
				elseif ($this->page === 'about') $this->saveAbout($data);
				elseif ($this->page === 'seo') {
					$this->saveSeo($data);
					$this->flashMessage('SEO nastavení bylo uloženo.', 'success');
					$this->redirect('default', ['page' => 'seo', 'path' => $data['seo_path']]);
				}
				elseif ($this->page === 'settings') {
					if (!$this->isSafeLink((string) $data['header_cta_url'])) {
						$form->addError('Odkaz hlavního tlačítka musí být interní cesta nebo platná adresa HTTP/HTTPS.');
						return;
					}
					foreach (['facebook', 'instagram', 'linkedin'] as $socialLink) {
						$url = trim((string) $data[$socialLink]);
						$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
						if ($url !== '' && ($scheme !== 'https' || filter_var($url, FILTER_VALIDATE_URL) === false)) {
							$form->addError('Odkazy na sociální sítě musí být platné adresy HTTPS.');
							return;
						}
					}
					$this->saveSettings([
						'company_name' => $data['company_name'], 'admin_email' => $data['admin_email'],
						'ico' => $data['ico'], 'dic' => $data['dic'], 'facebook' => $data['facebook'],
						'instagram' => $data['instagram'], 'linkedin' => $data['linkedin'],
						'header_cta_label' => $data['header_cta_label'], 'header_cta_url' => $data['header_cta_url'],
					], 'general');
				}
				else {
					$mapsUrl = trim((string) $data['google_maps_url']);
					$scheme = strtolower((string) parse_url($mapsUrl, PHP_URL_SCHEME));
					if ($mapsUrl !== '' && (!in_array($scheme, ['http', 'https'], true) || filter_var($mapsUrl, FILTER_VALIDATE_URL) === false)) {
						$form->addError('Odkaz na mapu musí být platná adresa HTTP nebo HTTPS.');
						return;
					}
					$this->saveContact($data);
				}
				$this->flashMessage('Změny byly uloženy.', 'success');
				$this->redirect('default', ['page' => $this->page]);
			} catch (\Nette\Application\AbortException $e) {
				throw $e;
			} catch (\Throwable $e) {
				error_log('Site editor save failed: ' . $e->getMessage());
				$form->addError('Změny se nepodařilo uložit. Zkontrolujte vyplněná pole a zkuste to znovu.');
			}
		};
		return $form;
	}

	private function addPageFields(Form $form): void
	{
		if ($this->page === 'home') {
			$form->addText('hero_kicker', 'Krátký horní nadpis')->setRequired();
			$form->addText('hero_line_one', 'Hlavní nadpis, první řádek')->setRequired();
			$form->addText('hero_line_two', 'Hlavní nadpis, druhý řádek')->setRequired();
			$form->addTextArea('hero_intro', 'Úvodní text')->setRequired();
			$form->addText('hero_image_alt', 'Alternativní text fotografie');
			$form->addUpload('hero_image', 'Úvodní fotografie')->addRule($form::MaxFileSize, 'Maximální velikost je 8 MB.', 8 * 1024 * 1024);
			$form->addText('benefits_kicker', 'Označení sekce')->setRequired();
			$form->addText('benefits_title_one', 'Hlavní nadpis, první řádek')->setRequired();
			$form->addText('benefits_title_two', 'Hlavní nadpis, druhý řádek')->setRequired();
			$form->addTextArea('benefits_intro', 'Úvodní text sekce')->setRequired();
			for ($item = 1; $item <= 4; $item++) {
				$form->addText("benefits_item_{$item}_title", "Výhoda {$item}, nadpis")->setRequired();
				$form->addTextArea("benefits_item_{$item}_text", "Výhoda {$item}, text")->setRequired();
			}
			$form->addText('stat_projects', 'Počet realizací')->setRequired();
			$form->addText('stat_years', 'Počet let zkušeností')->setRequired();
			$form->addText('stat_satisfaction', 'Spokojenost zákazníků')->setRequired();
			return;
		}

		if ($this->page === 'about') {
			$form->addText('about_title', 'Hlavní nadpis')->setRequired();
			$form->addText('about_subtitle', 'Podnadpis')->setRequired();
			$form->addTextArea('about_text', 'Text stránky')->setRequired();
			$form->addText('about_image_alt', 'Alternativní text fotografie');
			$form->addUpload('about_image', 'Hlavní fotografie stránky')->addRule($form::MaxFileSize, 'Maximální velikost je 8 MB.', 8 * 1024 * 1024);
			$form->addText('home_about_image_alt', 'Alternativní text fotografie úvodní stránky');
			$form->addUpload('home_about_image', 'Fotografie bloku na úvodní stránce')->addRule($form::MaxFileSize, 'Maximální velikost je 8 MB.', 8 * 1024 * 1024);
			return;
		}
		if ($this->page === 'seo') {
			$form->addSelect('seo_path', 'Stránka', self::SEO_PAGES)->setRequired();
			$form->addText('meta_title', 'Název ve vyhledávání');
			$form->addTextArea('meta_description', 'Popis pro vyhledávače');
			$form->addTextArea('keywords', 'Klíčová slova (nepovinné)');
			$form->addText('canonical_url', 'Kanonická URL (nepovinné)');
			$form->addText('og_title', 'Název při sdílení');
			$form->addTextArea('og_description', 'Popis při sdílení');
			$form->addText('og_image', 'Obrázek při sdílení (URL, nepovinné)');
			$form->addText('robots', 'Pravidla pro vyhledávače (robots)');
			return;
		}
		if ($this->page === 'settings') {
			$form->addText('company_name', 'Název společnosti')->setRequired();
			$form->addEmail('admin_email', 'E-mail administrátora')->setRequired();
			$form->addText('ico', 'IČO'); $form->addText('dic', 'DIČ');
			$form->addText('facebook', 'Facebook URL'); $form->addText('instagram', 'Instagram URL'); $form->addText('linkedin', 'LinkedIn URL');
			$form->addText('header_cta_label', 'Text hlavního tlačítka')->setRequired();
			$form->addText('header_cta_url', 'Odkaz hlavního tlačítka')->setRequired();
			return;
		}

		$form->addText('company_name', 'Název společnosti')->setRequired();
		$form->addText('phone', 'Telefon')->setRequired();
		$form->addEmail('email', 'E-mail')->setRequired();
		$form->addText('address', 'Adresa')->setRequired();
		$form->addText('hours', 'Otevírací doba');
			$form->addText('google_maps_url', 'Odkaz na mapu');
	}

	private function loadData(): array
	{
		if ($this->page === 'home') {
			return [
				'hero_kicker' => $this->setting('home_hero_kicker', 'STAVÍME VAŠI LEPŠÍ BUDOUCNOST'),
				'hero_line_one' => $this->setting('home_hero_line_one', 'VEŠKERÉ'),
				'hero_line_two' => $this->setting('home_hero_line_two', 'STAVEBNÍ PRÁCE'),
				'hero_intro' => $this->section('home_intro')['content'] ?? 'Kvalitní výstavba, rekonstrukce a modernizace pro váš domov i podnikání.',
				'hero_image_alt' => $this->setting('home_hero_image_alt', 'Moderní rodinný dům obklopený zelení'),
				'hero_image_preview' => $this->images->safeUrl($this->setting('home_hero_image', ''), 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80'),
				'benefits_kicker' => $this->setting('home_benefits_kicker', '03 / PROČ TIARA'),
				'benefits_title_one' => $this->setting('home_benefits_title_one', 'Jistota v každém'),
				'benefits_title_two' => $this->setting('home_benefits_title_two', 'detailu.'),
				'benefits_intro' => $this->setting('home_benefits_intro', 'Dobrá stavba začíná nasloucháním a končí výsledkem, který obstojí v čase.'),
				'benefits_item_1_title' => $this->setting('home_benefits_item_1_title', 'Kvalita a preciznost'),
				'benefits_item_1_text' => $this->setting('home_benefits_item_1_text', 'Pečlivé řemeslné provedení v každé vrstvě.'),
				'benefits_item_2_title' => $this->setting('home_benefits_item_2_title', 'Spolehlivé termíny'),
				'benefits_item_2_text' => $this->setting('home_benefits_item_2_text', 'Jasný plán a dohody, které platí.'),
				'benefits_item_3_title' => $this->setting('home_benefits_item_3_title', 'Osobní přístup'),
				'benefits_item_3_text' => $this->setting('home_benefits_item_3_text', 'Řešení připravené podle vašich potřeb.'),
				'benefits_item_4_title' => $this->setting('home_benefits_item_4_title', 'Moderní technologie'),
				'benefits_item_4_text' => $this->setting('home_benefits_item_4_text', 'Promyšlené materiály a ověřené postupy.'),
				'stat_projects' => $this->setting('stat_projects', '100+'),
				'stat_years' => $this->setting('stat_years', '10+'),
				'stat_satisfaction' => $this->setting('stat_satisfaction', '100%'),
			];
		}

		if ($this->page === 'about') {
			$about = $this->section('about');
			return [
				'about_title' => $about['title'] ?? 'Spolehlivý stavební partner',
				'about_subtitle' => $about['subtitle'] ?? 'TIARA s.r.o.',
				'about_text' => $about['content'] ?? '',
				'about_image_alt' => $this->setting('about_image_alt', 'Průběh stavby a práce stavebního týmu'),
				'about_image_preview' => $this->images->safeUrl($about['image_path'] ?? null, 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1500&q=84'),
				'home_about_image_alt' => $this->setting('home_about_image_alt', 'Stavební tým TIARA při práci na realizaci'),
				'home_about_image_preview' => $this->images->safeUrl($this->setting('home_about_image', ''), 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1200&q=82'),
			];
		}
		if ($this->page === 'settings') return [
			'company_name' => $this->setting('company_name', 'TIARA s.r.o.'),
			'admin_email' => $this->setting('admin_email', 'info@tiara-stavby.cz'),
			'ico' => $this->setting('ico', ''), 'dic' => $this->setting('dic', ''),
			'facebook' => $this->setting('facebook', ''), 'instagram' => $this->setting('instagram', ''),
			'linkedin' => $this->setting('linkedin', ''),
			'header_cta_label' => $this->setting('header_cta_label', 'Nezávazná poptávka'),
			'header_cta_url' => $this->setting('header_cta_url', '/#contact'),
		];
		if ($this->page === 'seo') {
			$path = (string) ($this->getParameter('path') ?? '/');
			if (!isset(self::SEO_PAGES[$path])) $path = '/';
			$row = $this->database->table('seo_metadata')->where('page_path', $path)->where('lang', 'cs')->fetch();
			$keywordsKey = $this->keywordsSettingKey($path);
			return array_merge([
				'seo_path' => $path, 'meta_title' => '', 'meta_description' => '', 'keywords' => $this->setting($keywordsKey, ''),
				'canonical_url' => '', 'og_title' => '', 'og_description' => '', 'og_image' => '', 'robots' => 'index,follow',
			], $row ? $row->toArray() : []);
		}

		return [
			'company_name' => $this->setting('company_name', 'TIARA s.r.o.'),
			'phone' => $this->setting('phone', '+420 000 000 000'),
			'email' => $this->setting('email', 'info@tiara-stavby.cz'),
			'address' => $this->setting('address', 'Česká republika'),
			'hours' => $this->setting('hours', 'Po–Pá 8:00–17:00'),
			'google_maps_url' => $this->setting('google_maps_url', ''),
			'admin_email' => $this->setting('admin_email', 'info@tiara-stavby.cz'),
		];
	}

	private function saveHome(array $data): void
	{
		$heroImage = $this->storeUpload($data['hero_image'] ?? null, $this->setting('home_hero_image', ''));
		$settings = [
			'home_hero_kicker' => $data['hero_kicker'], 'home_hero_line_one' => $data['hero_line_one'],
			'home_hero_line_two' => $data['hero_line_two'], 'home_hero_image' => $heroImage,
			'home_hero_image_alt' => $data['hero_image_alt'], 'stat_projects' => $data['stat_projects'],
			'stat_years' => $data['stat_years'], 'stat_satisfaction' => $data['stat_satisfaction'],
			'home_benefits_kicker' => $data['benefits_kicker'],
			'home_benefits_title_one' => $data['benefits_title_one'], 'home_benefits_title_two' => $data['benefits_title_two'],
			'home_benefits_intro' => $data['benefits_intro'],
		];
		for ($item = 1; $item <= 4; $item++) {
			$settings["home_benefits_item_{$item}_title"] = $data["benefits_item_{$item}_title"];
			$settings["home_benefits_item_{$item}_text"] = $data["benefits_item_{$item}_text"];
		}
		$this->saveSettings($settings, 'homepage');
		$this->saveSection('home_intro', ['content' => $data['hero_intro']]);
	}

	private function saveAbout(array $data): void
	{
		$current = $this->section('about');
		$image = $this->storeUpload($data['about_image'] ?? null, $current['image_path'] ?? '');
		$homeImage = $this->storeUpload($data['home_about_image'] ?? null, $this->setting('home_about_image', ''));
		$this->saveSection('about', [
			'title' => $data['about_title'], 'subtitle' => $data['about_subtitle'],
			'content' => $data['about_text'], 'image_path' => $image,
		]);
		$this->saveSettings([
			'about_image_alt' => $data['about_image_alt'],
			'home_about_image' => $homeImage,
			'home_about_image_alt' => $data['home_about_image_alt'],
		], 'content');
	}

	private function saveContact(array $data): void
	{
		$this->saveSettings([
			'company_name' => $data['company_name'], 'phone' => $data['phone'], 'email' => $data['email'],
			'address' => $data['address'], 'hours' => $data['hours'], 'google_maps_url' => $data['google_maps_url'],
		], 'contact');
	}

	private function saveSeo(array $data): void
	{
		$path = (string) $data['seo_path'];
		if (!isset(self::SEO_PAGES[$path])) throw new \RuntimeException('Stránka pro SEO není platná.');
		$keywords = (string) ($data['keywords'] ?? '');
		unset($data['seo_path'], $data['save'], $data['keywords']);
		$data['page_path'] = $path;
		$data['lang'] = 'cs';
		$row = $this->database->table('seo_metadata')->where('page_path', $path)->where('lang', 'cs')->fetch();
		if ($row) $row->update($data);
		else $this->database->table('seo_metadata')->insert($data);
		$this->saveSettings([$this->keywordsSettingKey($path) => $keywords], 'seo');
	}

	private function keywordsSettingKey(string $path): string
	{
		return 'seo_keywords_' . (trim(str_replace('/', '_', $path), '_') ?: 'home');
	}

	private function isSafeLink(string $url): bool
	{
		$url = trim($url);
		if (str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\') && !preg_match('/[\r\n]/', $url)) return true;
		$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
		return in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL) !== false;
	}

	private function storeUpload(mixed $upload, string $currentPath): string
	{
		if (!$upload instanceof FileUpload || !$upload->hasFile()) return $currentPath;
		$nextPath = $this->images->saveContentImage($upload);
		if ($currentPath !== $nextPath) $this->images->deleteContentImage($currentPath);
		return $nextPath;
	}

	private function saveSettings(array $values, string $group): void
	{
		foreach ($values as $key => $value) {
			$row = $this->database->table('settings')->where('setting_key', $key)->fetch();
			if ($row) $row->update(['setting_value' => (string) $value, 'setting_group' => $group]);
			else $this->database->table('settings')->insert(['setting_key' => $key, 'setting_value' => (string) $value, 'setting_group' => $group]);
		}
	}

	private function saveSection(string $key, array $values): void
	{
		$row = $this->database->table('page_sections')->where('section_key', $key)->where('lang', 'cs')->fetch();
		if ($row) {
			$row->update($values);
			return;
		}
		$this->database->table('page_sections')->insert(array_merge([
			'section_key' => $key, 'lang' => 'cs', 'title' => '', 'subtitle' => '',
			'content' => '', 'image_path' => null, 'active' => 1, 'sort_order' => 0,
		], $values));
	}

	private function section(string $key): ?array
	{
		$row = $this->database->table('page_sections')->where('section_key', $key)->where('lang', 'cs')->fetch();
		return $row ? $row->toArray() : null;
	}

	private function setting(string $key, string $fallback): string
	{
		return (string) ($this->database->table('settings')->where('setting_key', $key)->fetchField('setting_value') ?? $fallback);
	}
}
