<?php
namespace Solleer\Router;
class RegexModuleJson implements \Level2\Router\Rule {
    private $jsonModule;
    private $dice;

    public function __construct(\Config\Router\ModuleJson $moduleJson, \Dice\Dice $dice) {
        $this->moduleJson = $moduleJson;
        $this->dice = $dice;
    }

    public function find(array $route) {
        if (count($route) == 0 || $route[0] == '') return false;
        $moduleName = $route[0];

        $config = $this->moduleJson->getConfig($route);
        if (!$config) return false;
        $conditionsConfig = json_decode(json_encode($config->conditions ?? []), true);

        $newRoute = $this->getRoute($route, $conditionsConfig);
        $route = $newRoute ? array_merge([$moduleName], $newRoute) : $route;

        $authConfig = json_decode(json_encode($config->authorize ?? []), true);
        $authPass = $this->checkAuthorize($authConfig, $route);
        if ($authPass !== true) return $authPass;


        return $this->moduleJson->find($route);
    }

    private function getRoute($route, $config) {
        array_shift($route);
        foreach ($config as $routeName => $routeRegex) {
            $newRoute = $this->matchRoute($route, $routeRegex);
            if ($newRoute !== false) {
                if (empty($newRoute)) return $route;
                else return array_merge([$routeName], $newRoute);
            }
        }
        return false;
    }

    private function matchRoute(array $route, array $routeRegex) {
        $newRoute = [];
        foreach ($routeRegex as $key => $regex) {
            $result = preg_match($regex, $route[$key] ?? '', $matches);
            if ($result !== 1) return false;
            array_shift($matches);
            $newRoute = array_merge($newRoute, $matches);
        }
        return $newRoute;
    }

    private function checkAuthorize($config, $route) {
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
