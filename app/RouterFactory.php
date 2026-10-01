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
		$router->addRoute('admin/<section>[/<operation>[/<id \\d+>]]', 'Admin:Content:default');
		$router->addRoute('sitemap.xml', 'Front:Sitemap:default');
		$router->addRoute('', 'Front:Home:default');
		foreach (['o-nas' => 'about', 'sluzby' => 'services', 'realizace' => 'projects', 'reference' => 'gallery', 'kontakt' => 'contact', 'dekujeme' => 'thanks'] as $path => $action) {
			$router->addRoute($path, ['module' => 'Front', 'presenter' => 'Pages', 'action' => $action]);
		}
		$router->addRoute('sluzby/<slug>', 'Front:Pages:service');
		$router->addRoute('realizace/<id \\d+>', 'Front:Pages:project');
		$router->addRoute('<path .+>', 'Front:Error:default');
		return $router;
	}
}
