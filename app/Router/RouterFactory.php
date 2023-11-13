<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList();
        $router->addRoute('changes/<threshold>', 'Products:changes');
        $router->addRoute('products[/<page>]', 'Products:pages');
        $router->addRoute('config', 'Config:default');
        return $router;
    }
}
