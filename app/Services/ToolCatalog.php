<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use PDO;

final class ToolCatalog
{
    public function __construct(private readonly ?PDO $db = null) {}

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }

    public function list(array $filters = []): array
    {
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int)($filters['per_page'] ?? 24)));
        $where = ["t.status = 'published'", "t.is_active = TRUE", "t.visibility = 'public'"];
        $params = [];
        if (!empty($filters['category'])) {
            $where[] = 'c.slug = :category';
            $params['category'] = (string)$filters['category'];
        }
        if (!empty($filters['q'])) {
            $where[] = "(t.name_bn ILIKE :query OR t.name_en ILIKE :query OR t.slug ILIKE :query OR t.description_bn ILIKE :query OR t.description_en ILIKE :query)";
            $params['query'] = '%' . trim((string)$filters['q']) . '%';
        }
        $sqlWhere = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM tools t LEFT JOIN categories c ON c.id=t.category_id WHERE {$sqlWhere}");
        $count->execute($params);
        $total = (int)$count->fetchColumn();
        $sql = "SELECT t.id,t.slug,t.name_bn,t.name_en,t.description_bn,t.description_en,t.execution_type,t.version,t.status,t.input_schema,t.output_schema,c.slug AS category_slug,c.name_bn AS category_bn,c.name_en AS category_en
                FROM tools t LEFT JOIN categories c ON c.id=t.category_id
                WHERE {$sqlWhere} ORDER BY c.sort_order,t.name_en LIMIT :limit OFFSET :offset";
        $stmt = $this->connection()->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        return ['data' => $stmt->fetchAll(), 'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => max(1, (int)ceil($total / $perPage))]];
    }

    public function findPublished(string $slug): ?array
    {
        $stmt = $this->connection()->prepare("SELECT t.id,t.slug,t.name_bn,t.name_en,t.description_bn,t.description_en,t.execution_type,t.version,t.status,t.input_schema,t.output_schema,c.slug AS category_slug,c.name_bn AS category_bn,c.name_en AS category_en FROM tools t LEFT JOIN categories c ON c.id=t.category_id WHERE t.slug=:slug AND t.status='published' AND t.is_active=TRUE AND t.visibility='public' LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $tool = $stmt->fetch();
        return $tool ?: null;
    }

    public function categories(): array
    {
        $stmt = $this->connection()->query("SELECT c.slug,c.name_bn,c.name_en,c.sort_order,COUNT(t.id) FILTER (WHERE t.status='published' AND t.is_active=TRUE) AS tool_count FROM categories c LEFT JOIN tools t ON t.category_id=c.id WHERE c.is_active=TRUE GROUP BY c.id ORDER BY c.sort_order,c.name_en");
        return $stmt->fetchAll();
    }
}
