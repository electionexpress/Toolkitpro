<?php
declare(strict_types=1);
namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $quoted = preg_quote($path, '~');
        $regex = preg_replace('/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/', '(?P<$1>[^/]+)', $quoted);
        $this->routes['GET'][] = ['#^' . $regex . '$#', $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        foreach ($this->routes[$method] ?? [] as [$pattern, $handler]) {
            if (preg_match($pattern, $path, $matches) !== 1) continue;
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) $params[] = rawurldecode($value);
            }
            $handler(...$params);
            return;
        }
        http_response_code(404);
        if (str_starts_with($path, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found.']], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo '404 - Page not found';
    }
}
