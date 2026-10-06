<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\InquiryRepository;
use App\Model\ImageManager;
use App\Model\NavigationRepository;
use App\Model\PageRepository;
use App\Model\SeoRepository;
use App\Model\ServiceRepository;
use App\Model\SettingRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Utils\Random;

abstract class BasePresenter extends Presenter
{
	protected SettingRepository $settings;
	protected NavigationRepository $navigation;
	protected PageRepository $pages;
	protected ServiceRepository $services;
	protected InquiryRepository $inquiries;
	protected SeoRepository $seo;
	protected ImageManager $imageManager;

	public function injectBase(
		SettingRepository $settings,
		NavigationRepository $navigation,
		PageRepository $pages,
		ServiceRepository $services,
		InquiryRepository $inquiries,
		SeoRepository $seo,
		ImageManager $imageManager,
	): void {
		$this->settings = $settings;
		$this->navigation = $navigation;
		$this->pages = $pages;
		$this->services = $services;
		$this->inquiries = $inquiries;
		$this->seo = $seo;
		$this->imageManager = $imageManager;
	}

	protected function startup(): void
	{
		parent::startup();
		$this->getSession()->start();
	}

	protected function beforeRender(): void
	{
		parent::beforeRender();
		$path = $this->getHttpRequest()->getUrl()->getPath() ?: '/';
		$this->template->navigationItems = $this->navigation->all('cs');
		$this->template->company = $this->settings->all();
		$this->template->labels = $this->labels();
		$this->template->imageManager = $this->imageManager;
		$currentPath = $this->getHttpRequest()->getUrl()->getPath();
		$this->template->currentPath = $currentPath;
		$activeNavigation = [];
		foreach ($this->template->navigationItems as $item) {
			$target = rtrim((string) $item['url'], '/') ?: '/';
			$current = rtrim($currentPath, '/') ?: '/';
			$activeNavigation[$item['id']] = $target === $current || ($target !== '/' && str_starts_with($current, $target . '/'));
		}
		$this->template->activeNavigation = $activeNavigation;
		$this->template->jsonLd = json_encode([
			'@context' => 'https://schema.org', '@type' => 'GeneralContractor', 'name' => $this->settings->get('company_name', 'TIARA s.r.o.'),
			'description' => 'Komplexní stavební práce, výstavba, rekonstrukce a modernizace.',
			'telephone' => $this->settings->get('phone'), 'email' => $this->settings->get('email'),
			'address' => ['@type' => 'PostalAddress', 'addressLocality' => $this->settings->get('address'), 'addressCountry' => 'CZ'],
			'url' => 'https://tiara-stavby.cz',
		], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
		$meta = $this->seo->forPath($path, 'cs');
		$keywordsKey = 'seo_keywords_' . (trim(str_replace('/', '_', $path), '_') ?: 'home');
		$this->template->metaKeywords = $this->settings->get($keywordsKey);
		if ($meta) {
			$this->template->metaTitle = $meta['meta_title'];
			$this->template->metaDescription = $meta['meta_description'];
			$this->template->ogTitle = $meta['og_title'] ?: $meta['meta_title'];
			$this->template->ogDescription = $meta['og_description'] ?: $meta['meta_description'];
			$this->template->ogImage = $meta['og_image'];
			$this->template->canonical = $meta['canonical_url'];
			$this->template->robots = $meta['robots'];
		}
	}

	protected function createComponentInquiryForm(): Form
	{
		$form = new Form;
		$form->setHtmlAttribute('class', 'contact-form');
		$required = 'Vyplňte prosím toto pole.';
		$services = [];
		foreach ($this->services->all('cs') as $service) $services[$service['title']] = $service['title'];
		$form->addText('name', 'Vaše jméno')->setRequired($required)->addRule($form::MaxLength, null, 180);
		$form->addEmail('email', 'E-mail')->setRequired($required)->addRule($form::MaxLength, null, 190);
		$form->addText('phone', 'Telefon (nepovinné)')->addRule($form::MaxLength, null, 60);
		$form->addSelect('service', 'Typ služby', $services)->setPrompt('Vyberte službu');
		$form->addTextArea('message', 'Napište nám o svém projektu')->setRequired($required)->addRule($form::MinLength, 'Zpráva je příliš krátká.', 10)->addRule($form::MaxLength, null, 10000);
		$form->addCheckbox('consent', 'Souhlasím se zpracováním osobních údajů.')->setRequired('Pro odeslání je nutný souhlas.');
		$form->addText('website')->setHtmlAttribute('class', 'hp-field')->setHtmlAttribute('tabindex', '-1')->setHtmlAttribute('autocomplete', 'off');
		$submission = $this->getSession()->getSection('inquiry');
		if (!$submission->get('submissionToken')) $submission->set('submissionToken', Random::generate(40));
		$form->addHidden('submissionToken')->setDefaultValue((string) $submission->get('submissionToken'));
		$form->addProtection('Formulář vypršel. Zkuste to prosím znovu.');
		$form->addSubmit('send', 'Odeslat poptávku')->setHtmlAttribute('class', 'button button--gold');
		$form->onSuccess[] = function (Form $form, \stdClass $values): void {
			if (trim((string) $values->website) !== '') {
				$this->redirectToContact();
			}
			$submission = $this->getSession()->getSection('inquiry');
			$expectedToken = $submission->get('submissionToken');
			if (!is_string($expectedToken) || !hash_equals($expectedToken, (string) $values->submissionToken)) {
				$this->flashMessage('Vaši poptávku už jsme přijali.', 'success');
				$this->redirectToContact();
			}
			$ip = $this->getHttpRequest()->getRemoteAddress() ?: 'unknown';
			try {
				if ($this->inquiries->countRecent($ip) >= 5) {
				$form->addError('Zkuste to prosím později.');
					return;
				}
				$this->inquiries->create([
					'lang' => 'cs',
					'name' => trim($values->name),
					'email' => trim($values->email),
					'phone' => trim($values->phone) ?: null,
					'service' => $values->service ?: null,
					'message' => trim($values->message),
					'consent' => (int) $values->consent,
					'ip_address' => $ip,
				]);
				$submission->set('submissionToken', Random::generate(40));
				$this->notifyAdmin($values);
				$this->flashMessage('Děkujeme! Vaše zpráva byla odeslána. Brzy se vám ozveme.', 'success');
				$this->redirectToContact();
			} catch (\Nette\Application\AbortException $e) {
				throw $e;
			} catch (\Throwable $e) {
				error_log('Inquiry submission failed: ' . $e->getMessage());
				$form->addError('Poptávku se nepodařilo odeslat. Zkuste to prosím znovu.');
			}
		};
		return $form;
	}

	private function redirectToContact(): never
	{
		$this->redirect('Home:default#contact');
	}

	private function notifyAdmin(\stdClass $values): void
	{
		$to = $this->settings->get('admin_email', 'info@tiara-stavby.cz');
		$subject = mb_encode_mimeheader('Nová poptávka z webu TIARA', 'UTF-8');
		$message = "Jméno: {$values->name}\nE-mail: {$values->email}\nTelefon: {$values->phone}\nSlužba: {$values->service}\n\n{$values->message}";
		$headers = 'From: web@tiara-stavby.cz' . "\r\n" . 'Reply-To: ' . str_replace(["\r", "\n"], '', $values->email) . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
		@mail($to, $subject, $message, $headers);
	}

	private function labels(): array
	{
		return [
			'home' => 'Domů', 'about' => 'O nás', 'services' => 'Služby', 'projects' => 'Realizace',
			'quote' => 'Nezávazná poptávka', 'more' => 'Zjistit více', 'allProjects' => 'Všechny realizace', 'allServices' => 'Všechny služby', 'contactUs' => 'Kontaktujte nás', 'phone' => 'Telefon', 'email' => 'E-mail', 'address' => 'Adresa', 'hours' => 'Pracovní doba', 'send' => 'Odeslat poptávku', 'location' => 'Lokalita', 'year' => 'Rok', 'category' => 'Kategorie', 'back' => 'Zpět na realizace', 'step' => 'Pojďme probrat váš projekt',
		];
	}
}
