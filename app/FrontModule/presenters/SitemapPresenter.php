<?php

declare(strict_types=1);

namespace App\FrontModule\presenters;

use App\Model\ProjectRepository;
use App\Model\ServiceRepository;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;

final class SitemapPresenter extends Presenter
{
	public function __construct(private ProjectRepository $projects, private ServiceRepository $services) { parent::__construct(); }

	public function actionDefault(): void
	{
		$baseUrl = $this->getHttpRequest()->getUrl()->getHostUrl();
		$urls = [];
		foreach (['/', '/o-nas', '/sluzby', '/realizace', '/kontakt'] as $path) $urls[] = $path;
		foreach ($this->services->all('cs') as $service) $urls[] = '/sluzby/' . rawurlencode($service['slug']);
		foreach ($this->projects->all('cs') as $project) $urls[] = '/realizace/' . (int) $project['id'];
		$body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		foreach (array_unique($urls) as $path) $body .= '  <url><loc>' . htmlspecialchars($baseUrl . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc></url>\n";
		$body .= "</urlset>\n";
		$this->getHttpResponse()->setContentType('application/xml', 'utf-8');
		$this->getHttpResponse()->setHeader('Cache-Control', 'public, max-age=3600');
		$this->sendResponse(new TextResponse($body));
	}
}
