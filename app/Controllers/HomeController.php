<?php
declare(strict_types=1);
namespace App\Controllers;
final class HomeController
{
    public function index(): void
    {
        $title = htmlspecialchars(getenv('APP_NAME') ?: 'ToolkitPro', ENT_QUOTES, 'UTF-8');
        require dirname(__DIR__, 2) . '/resources/views/home/index.php';
    }
}