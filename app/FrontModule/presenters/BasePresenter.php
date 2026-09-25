<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\InquiryRepository;
use App\Model\NavigationRepository;
use App\Model\PageRepository;
use App\Model\SeoRepository;
use App\Model\ServiceRepository;
use App\Model\SettingRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	public string $lang = 'cs';
	protected SettingRepository $settings;
	protected NavigationRepository $navigation;
	protected PageRepository $pages;
	protected ServiceRepository $services;
	protected InquiryRepository $inquiries;
	protected SeoRepository $seo;

	public function injectBase(
		SettingRepository $settings,
		NavigationRepository $navigation,
		PageRepository $pages,
		ServiceRepository $services,
		InquiryRepository $inquiries,
		SeoRepository $seo,
	): void {
		$this->settings = $settings;
		$this->navigation = $navigation;
		$this->pages = $pages;
		$this->services = $services;
		$this->inquiries = $inquiries;
		$this->seo = $seo;
	}

	protected function startup(): void
	{
		parent::startup();
		$lang = (string) $this->getParameter('lang', 'cs');
		$this->lang = in_array($lang, ['cs', 'en'], true) ? $lang : 'cs';
	}

	protected function beforeRender(): void
	{
		parent::beforeRender();
		$path = preg_replace('#^/(cs|en)(?=/|$)#', '', $this->getHttpRequest()->getUrl()->getPath()) ?: '/';
		$this->template->lang = $this->lang;
		$this->template->navigationItems = $this->navigation->all($this->lang);
		$this->template->company = $this->settings->all();
		$this->template->labels = $this->labels();
		$meta = $this->seo->forPath($path, $this->lang);
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
		$english = $this->lang === 'en';
		$required = $english ? 'Please fill in this field.' : 'Vyplňte prosím toto pole.';
		$services = [];
		foreach ($this->services->all($this->lang) as $service) $services[$service['title']] = $service['title'];
		$form->addText('name', $english ? 'Your name' : 'Vaše jméno')->setRequired($required)->addRule($form::MaxLength, null, 180);
		$form->addEmail('email', $english ? 'Email address' : 'E-mail')->setRequired($required)->addRule($form::MaxLength, null, 190);
		$form->addText('phone', $english ? 'Phone (optional)' : 'Telefon (nepovinné)')->addRule($form::MaxLength, null, 60);
		$form->addSelect('service', $english ? 'Service' : 'Typ služby', $services)->setPrompt($english ? 'Choose a service' : 'Vyberte službu');
		$form->addTextArea('message', $english ? 'Tell us about your project' : 'Napište nám o svém projektu')->setRequired($required)->addRule($form::MinLength, $english ? 'Message is too short.' : 'Zpráva je příliš krátká.', 10)->addRule($form::MaxLength, null, 10000);
		$form->addCheckbox('consent', $english ? 'I agree to the processing of my personal data.' : 'Souhlasím se zpracováním osobních údajů.')->setRequired($english ? 'Please provide your consent.' : 'Pro odeslání je nutný souhlas.');
		$form->addText('website')->setHtmlAttribute('class', 'hp-field')->setHtmlAttribute('tabindex', '-1')->setHtmlAttribute('autocomplete', 'off');
		$form->addProtection($english ? 'The form has expired. Please try again.' : 'Formulář vypršel. Zkuste to prosím znovu.');
		$form->addSubmit('send', $english ? 'Send inquiry' : 'Odeslat poptávku')->setHtmlAttribute('class', 'button button--gold');
		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($english): void {
			if (trim((string) $values->website) !== '') {
				$this->redirect('thanks', ['lang' => $this->lang]);
			}
			$ip = $this->getHttpRequest()->getRemoteAddress() ?: 'unknown';
			try {
				if ($this->inquiries->countRecent($ip) >= 5) {
					$form->addError($english ? 'Please try again later.' : 'Zkuste to prosím později.');
					return;
				}
				$this->inquiries->create([
					'lang' => $this->lang,
					'name' => trim($values->name),
					'email' => trim($values->email),
					'phone' => trim($values->phone) ?: null,
					'service' => $values->service ?: null,
					'message' => trim($values->message),
					'consent' => (int) $values->consent,
					'ip_address' => $ip,
				]);
				$this->notifyAdmin($values);
				$this->flashMessage($english ? 'Thank you. We will be in touch shortly.' : 'Děkujeme. Brzy se vám ozveme.', 'success');
				$this->redirect('thanks', ['lang' => $this->lang]);
			} catch (\Throwable $e) {
				error_log('Inquiry submission failed: ' . $e->getMessage());
				$form->addError($english ? 'We could not send your inquiry. Please try again.' : 'Poptávku se nepodařilo odeslat. Zkuste to prosím znovu.');
			}
		};
		return $form;
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
		return $this->lang === 'en' ? [
			'home' => 'Home', 'about' => 'About us', 'services' => 'Services', 'projects' => 'Projects', 'references' => 'References', 'contact' => 'Contact',
			'quote' => 'Request a quote', 'more' => 'Discover more', 'allProjects' => 'All projects', 'allServices' => 'Our services', 'contactUs' => 'Contact us', 'phone' => 'Phone', 'email' => 'Email', 'address' => 'Address', 'hours' => 'Opening hours', 'send' => 'Send inquiry', 'location' => 'Location', 'year' => 'Year', 'category' => 'Category', 'back' => 'Back to projects', 'step' => 'Let’s talk about your project',
		] : [
			'home' => 'Domů', 'about' => 'O nás', 'services' => 'Služby', 'projects' => 'Realizace', 'references' => 'Reference', 'contact' => 'Kontakt',
			'quote' => 'Nezávazná poptávka', 'more' => 'Zjistit více', 'allProjects' => 'Všechny realizace', 'allServices' => 'Naše služby', 'contactUs' => 'Kontaktujte nás', 'phone' => 'Telefon', 'email' => 'E-mail', 'address' => 'Adresa', 'hours' => 'Pracovní doba', 'send' => 'Odeslat poptávku', 'location' => 'Lokalita', 'year' => 'Rok', 'category' => 'Kategorie', 'back' => 'Zpět na realizace', 'step' => 'Pojďme probrat váš projekt',
		];
	}
}
