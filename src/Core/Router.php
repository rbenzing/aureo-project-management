<?php
namespace App\Core;

class Router
{
    private $routes = [];

    public function add($route, $params)
    {
        $this->routes[$route] = $params;
    }

    public function dispatch($requestMethod, $urlSegments)
    {
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST' ? true : false;
        $segments = $urlSegments;
        $data = [];

        if (empty($segments[0])) {
            $segments[0] = 'dashboard';
        }

        if (isset($segments[1])) {
            $actionName = $segments[1];
            if (isset($segments[2]) && $segments[1] !== 'page') {
                $data['id'] = $segments[2];
            }
        }

        if (isset($segments[2]) && $segments[1] === 'page') {
            $data['page'] = $segments[2];
        }

        // Check if the route exists
        if (!array_key_exists($segments[0], $this->routes)) {
            throw new \Exception('Page not found', 404);
        }
        
        if (isset($segments[2]) && is_numeric($segments[2])) {
            array_pop($segments);
            if ($segments[1] === 'page') {
                $segments[2] = ':page';
            } else {
                $segments[2] = ':id';
            }
        }
        
        $url = implode('/', $segments);
        $params = $this->routes[$url];
        
        $actionName = $params['action'] ?? 'index';
        $controllerName = ucfirst($params['controller']) . 'Controller';

        // Check if the controller class exists
        $controllerClass = '\\App\\Controllers\\' . $controllerName;
        if (!class_exists($controllerClass)) {
            throw new \Exception('Controller not found', 404);
        }

        $controller = new $controllerClass();

        // Check if the action method exists
        if (!method_exists($controller, $actionName)) {
            throw new \Exception('Action not found', 404);
        }

        call_user_func([$controller, $actionName], $requestMethod, $isPost ? $_POST : $data);
    }
}