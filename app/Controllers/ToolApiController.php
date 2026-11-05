<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Services\ToolCatalog;
use PDOException;
use Throwable;

final class ToolApiController
{
    public function index(): void
    {
        try {
            $result = (new ToolCatalog())->list([
                'q' => $_GET['q'] ?? '',
                'category' => $_GET['category'] ?? '',
                'page' => $_GET['page'] ?? 1,
                'per_page' => $_GET['per_page'] ?? 24,
            ]);
            $this->json(['data' => $result['data'], 'meta' => $result['meta']]);
        } catch (Throwable $e) {
            error_log('ToolkitPro tool catalog: ' . $e->getMessage());
            $this->json(['error' => ['code' => 'CATALOG_UNAVAILABLE', 'message' => 'টুল ক্যাটালগ সাময়িকভাবে পাওয়া যাচ্ছে না।']], 503);
        }
    }

    public function show(string $slug): void
    {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $this->json(['error' => ['code' => 'INVALID_SLUG', 'message' => 'Invalid tool identifier.']], 400);
            return;
        }
        try {
            $tool = (new ToolCatalog())->findPublished($slug);
            if ($tool === null) {
                $this->json(['error' => ['code' => 'TOOL_NOT_FOUND', 'message' => 'টুলটি পাওয়া যায়নি।']], 404);
                return;
            }
            $this->json(['data' => $tool]);
        } catch (Throwable $e) {
            error_log('ToolkitPro tool lookup: ' . $e->getMessage());
            $this->json(['error' => ['code' => 'CATALOG_UNAVAILABLE', 'message' => 'টুল তথ্য সাময়িকভাবে পাওয়া যাচ্ছে না।']], 503);
        }
    }

    public function categories(): void
    {
        try {
            $this->json(['data' => (new ToolCatalog())->categories()]);
        } catch (Throwable $e) {
            error_log('ToolkitPro categories: ' . $e->getMessage());
            $this->json(['error' => ['code' => 'CATALOG_UNAVAILABLE', 'message' => 'ক্যাটাগরি সাময়িকভাবে পাওয়া যাচ্ছে না।']], 503);
        }
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=30, stale-while-revalidate=60');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
