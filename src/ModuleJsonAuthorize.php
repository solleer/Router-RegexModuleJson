<?php

namespace Solleer\Router;
use Dice\Dice;

class ModuleJsonAuthorize {
    private $dice;

    public function __construct(Dice $dice) {
        $this->dice = $dice;
    }

    public function checkAuthorize($config, $route) {
        foreach ($config as $item) {
            if ($this->checkAuthRoutes($item['routes'], $route)) {
                $authObj = $this->dice->create($item['instanceOf']);

                list($func, $params) = $item['call'];

                if (!$authObj->{$func}(...$params)) return $this->dice->create($item['redirect']);
            }
        }

        return true;
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