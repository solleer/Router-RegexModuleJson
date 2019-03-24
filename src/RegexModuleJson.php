<?php
namespace Solleer\Router;
class RegexModuleJson implements \Level2\Router\Rule {
    private $moduleJson;
    private $authorize;

    public function __construct(\Level2\Router\Config\ModuleJson $moduleJson, ModuleJsonAuthorize $authorize) {
        $this->moduleJson = $moduleJson;
        $this->authorize = $authorize;
    }

    public function find(array $route) {
        if (count($route) == 0 || $route[0] == '') return false;
        $moduleName = $route[0];

        $config = $this->moduleJson->getConfig($route);
        if (!$config) return false;
        $conditionsConfig = json_decode(json_encode($config['conditions'] ?? []), true);

        $newRoute = $this->getRoute($route, $conditionsConfig);
        $route = $newRoute ? array_merge([$moduleName], $newRoute) : $route;

        $authConfig = json_decode(json_encode($config['authorize'] ?? []), true);
        $authPass = $this->authorize->checkAuthorize($authConfig, $route);
        if ($authPass !== true) return $authPass;


        return $this->moduleJson->find($route);
    }

    private function getRoute($route, $config) {
        array_shift($route);
        foreach ($config as $routeName => $routeRegex) {
            $newRoute = $this->matchRoute($route, $routeRegex);
            if ($newRoute !== false) {
                return array_merge([$routeName], $newRoute);
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
}
