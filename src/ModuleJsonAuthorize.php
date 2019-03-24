<?php

namespace Solleer\Router;
use Dice\Dice;

class ModuleJsonAuthorize {
    private $dice;

    public function __construct(Dice $dice) {
        $this->dice = $dice;
    }

    public function checkAuthorize($config, $route) {
        $matched = false;
        foreach ($config as $item) {
            if ($this->checkAuthRoutes($item['routes'], $route)) {
                $matched = $item;
                break;
            }
        }

        if (!$matched) return true;

        $authObj = $this->dice->create($matched['instanceOf']);

        list($func, $params) = $matched['call'];

        if ($authObj->{$func}(...$params)) return true;
        else return $this->dice->create($matched['redirect']);

    }

    private function checkAuthRoutes($routes, $route) {
        $route[1] = $route[1] ?? '';
        foreach ($routes as $routeItem){
            if ($routeItem === $route[1]) // They have the same route name
                return true;
        }

        return false;
    }
}