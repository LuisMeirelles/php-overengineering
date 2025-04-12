<?php

namespace App\Core\Routing;

use App\Core\Request;

class DynamicRouteMatcher
{
    private array $dynamicRouteCache;

    public function match(Request $request, array $routes): ?callable
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        if (isset($this->dynamicRouteCache[$method->value][$uri])) {
            return $this->dynamicRouteCache[$method->value][$uri];
        }

        foreach ($routes as $route => $action) {
            $routeRegex = addcslashes($route, '/');
            $routeRegex = preg_replace('/(\/:\w+)/', '/([^\/]+)', $routeRegex);

            preg_match("/^$routeRegex$/", $request->getUri(), $matches);
            array_shift($matches);

            if ($matches) {
                $params = [];

                preg_match_all('/\/:(\w+)/', $route, $routeParamNamesMatches);
                $routeParamNames = $routeParamNamesMatches[1];

                foreach ($matches as $index => $match) {
                    $params[$routeParamNames[$index]] = $match;
                }

                $request->setRouteParams($params);

                $this->dynamicRouteCache[$method->value][$route] = $action;

                return $action;
            }
        }

        return null;
    }
}