<?php

declare(strict_types=1);

namespace App;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('admin', 'Admin:Dashboard:default');
		$router->addRoute('admin/login', 'Admin:Login:default');
		$router->addRoute('admin/logout', 'Admin:Dashboard:logout');
		$router->addRoute('admin/<section>[/<operation>[/<id \\d+>]]', 'Admin:Content:default');
		$router->addRoute('', ['module' => 'Front', 'presenter' => 'Home', 'action' => 'default', 'lang' => 'cs']);

		foreach (['cs', 'en'] as $lang) {
			$prefix = $lang . '/';
			$router->addRoute($lang, ['module' => 'Front', 'presenter' => 'Home', 'action' => 'default', 'lang' => $lang]);
			$routes = $lang === 'cs'
				? ['o-nas' => 'about', 'sluzby' => 'services', 'realizace' => 'projects', 'reference' => 'gallery', 'kontakt' => 'contact', 'dekujeme' => 'thanks']
				: ['about' => 'about', 'services' => 'services', 'projects' => 'projects', 'references' => 'gallery', 'contact' => 'contact', 'thank-you' => 'thanks'];
			foreach ($routes as $path => $action) {
				$router->addRoute($prefix . $path, ['module' => 'Front', 'presenter' => 'Pages', 'action' => $action, 'lang' => $lang]);
			}
			$servicePath = $lang === 'cs' ? 'sluzby' : 'services';
			$projectPath = $lang === 'cs' ? 'realizace' : 'projects';
			$router->addRoute($prefix . $servicePath . '/<slug>', ['module' => 'Front', 'presenter' => 'Pages', 'action' => 'service', 'lang' => $lang]);
			$router->addRoute($prefix . $projectPath . '/<id \\d+>', ['module' => 'Front', 'presenter' => 'Pages', 'action' => 'project', 'lang' => $lang]);
		}
		$router->addRoute('<path .+>', 'Front:Error:default');
		return $router;
	}
}
