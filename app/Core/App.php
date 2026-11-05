<?php
declare(strict_types=1);
namespace App\Core;

use App\Controllers\HomeController;
use App\Controllers\HealthController;
use App\Controllers\ToolApiController;

final class App
{
    public function run(): void
    {
        $router = new Router();
        $tools = new ToolApiController();
        $router->get('/', [new HomeController(), 'index']);
        $router->get('/health', [new HealthController(), 'index']);
        $router->get('/api/v1/tools', [$tools, 'index']);
        $router->get('/api/v1/categories', [$tools, 'categories']);
        $router->get('/api/v1/tools/{slug}', [$tools, 'show']);
        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
    }
}
