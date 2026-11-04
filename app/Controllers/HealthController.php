<?php
declare(strict_types=1);
namespace App\Controllers;
final class HealthController
{
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'service' => 'ToolkitPro'], JSON_UNESCAPED_UNICODE);
    }
}