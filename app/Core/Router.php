<?php
declare(strict_types=1);
namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][] = ['path' => $path, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $requestPath = trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        $requestParts = $requestPath === '' ? [] : explode('/', $requestPath);

        foreach ($this->routes[$method] ?? [] as $route) {
            $routePath = trim($route['path'], '/');
            $routeParts = $routePath === '' ? [] : explode('/', $routePath);
            if (count($requestParts) !== count($routeParts)) continue;

            $params = [];
            $matched = true;
            foreach ($routeParts as $index => $part) {
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $capture) === 1) {
                    $params[] = rawurldecode($requestParts[$index]);
                } elseif ($part !== $requestParts[$index]) {
                    $matched = false;
                    break;
                }
            }
            if (!$matched) continue;
            ($route['handler'])(...$params);
            return;
        }

        http_response_code(404);
        if (str_starts_with('/' . $requestPath, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found.']], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo '404 - Page not found';
    }
}
