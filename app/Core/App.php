<?php
declare(strict_types=1);
namespace App\Core;
use App\Controllers\HomeController;
use App\Controllers\HealthController;
final class App
{
    public function run(): void
    {
        $router = new Router();
        $router->get('/', [new HomeController(), 'index']);
        $router->get('/health', [new HealthController(), 'index']);
        $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
    }
}