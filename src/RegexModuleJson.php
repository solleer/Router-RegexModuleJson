<?php
namespace Solleer\Router;
class RegexModuleJson implements \Level2\Router\Rule {
    private $jsonModule;

    public function __construct(\Config\Router\ModuleJson $moduleJson) {
        $this->moduleJson = $moduleJson;
    }

    public function find(array $route) {
        if (count($route) == 0 || $route[0] == '') return false;
        $moduleName = $route[0];

        $config = $this->moduleJson->getConfig($route);
        if (!$config) return false;
        $config = json_decode(json_encode($config->conditions ?? []), true);

        $newRoute = $this->getRoute($route, $config);
        $route = array_merge([$moduleName], $newRoute !== false ? $newRoute : $route);

        return $this->moduleJson->find($route);
    }

    private function getRoute($route, $config) {
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
            $result = preg_match($regex, $route[$key], $matches);
            if ($result !== 1) return false;
            $newRoute = array_merge($newRoute, $matches);
        }
        return $newRoute;
    }
}
