<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class ToolDefinitionService
{
    public function __construct(private readonly ?PDO $db = null) {}

    private function connection(): PDO { return $this->db ?? Database::connection(); }

    public function createDraft(array $definition, int $actorUserId): int
    {
        $data = $this->normalize($definition);
        $db = $this->connection();
        $db->beginTransaction();
        try {
            $category = $db->prepare('SELECT id FROM categories WHERE slug=:slug AND is_active=TRUE');
            $category->execute(['slug' => $data['category_slug']]);
            $categoryId = $category->fetchColumn();
            if (!$categoryId) throw new InvalidArgumentException('Unknown or inactive category.');

            $insert = $db->prepare("INSERT INTO tools (category_id,slug,name_bn,name_en,description_bn,description_en,execution_type,version,is_active,status,visibility,input_schema,output_schema,runtime_key,config)
                VALUES (:category_id,:slug,:name_bn,:name_en,:description_bn,:description_en,:execution_type,:version,FALSE,'draft',:visibility,CAST(:input_schema AS JSONB),CAST(:output_schema AS JSONB),:runtime_key,CAST(:config AS JSONB)) RETURNING id");
            $insert->execute([
                'category_id' => $categoryId, 'slug' => $data['slug'], 'name_bn' => $data['name_bn'],
                'name_en' => $data['name_en'], 'description_bn' => $data['description_bn'],
                'description_en' => $data['description_en'], 'execution_type' => $data['execution_type'],
                'version' => $data['version'], 'visibility' => $data['visibility'],
                'input_schema' => json_encode($data['input_schema'], JSON_THROW_ON_ERROR),
                'output_schema' => json_encode($data['output_schema'], JSON_THROW_ON_ERROR),
                'runtime_key' => $data['runtime_key'],
                'config' => json_encode($data['config'], JSON_THROW_ON_ERROR),
            ]);
            $id = (int)$insert->fetchColumn();
            $version = $db->prepare('INSERT INTO tool_versions (tool_id,version,definition,changelog,created_by) VALUES (:tool_id,:version,CAST(:definition AS JSONB),:changelog,:actor)');
            $version->execute(['tool_id' => $id, 'version' => $data['version'], 'definition' => json_encode($data, JSON_THROW_ON_ERROR), 'changelog' => 'Initial draft', 'actor' => $actorUserId]);
            $this->audit($db, $actorUserId, 'tool.created', $id, ['slug' => $data['slug']]);
            $db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public function publish(int $toolId, int $actorUserId): void
    {
        $db = $this->connection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE tools SET status='published',is_active=TRUE,updated_at=NOW() WHERE id=:id AND status IN ('draft','review','published') RETURNING slug");
            $stmt->execute(['id' => $toolId]);
            $slug = $stmt->fetchColumn();
            if (!$slug) throw new RuntimeException('Tool not found or cannot be published.');
            $this->audit($db, $actorUserId, 'tool.published', $toolId, ['slug' => $slug]);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public function disable(int $toolId, int $actorUserId): void
    {
        $db = $this->connection();
        $stmt = $db->prepare("UPDATE tools SET status='disabled',is_active=FALSE,updated_at=NOW() WHERE id=:id RETURNING slug");
        $stmt->execute(['id' => $toolId]);
        $slug = $stmt->fetchColumn();
        if (!$slug) throw new RuntimeException('Tool not found.');
        $this->audit($db, $actorUserId, 'tool.disabled', $toolId, ['slug' => $slug]);
    }

    private function normalize(array $d): array
    {
        $slug = strtolower(trim((string)($d['slug'] ?? '')));
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) throw new InvalidArgumentException('Tool slug must use lowercase letters, numbers and hyphens.');
        $nameBn = trim((string)($d['name_bn'] ?? ''));
        $nameEn = trim((string)($d['name_en'] ?? ''));
        if ($nameBn === '' || $nameEn === '' || mb_strlen($nameBn) > 180 || mb_strlen($nameEn) > 180) throw new InvalidArgumentException('Both Bengali and English tool names are required (maximum 180 characters).');
        $execution = (string)($d['execution_type'] ?? 'client');
        if (!in_array($execution, ['client','server','api'], true)) throw new InvalidArgumentException('Unsupported execution type.');
        $version = (string)($d['version'] ?? '1.0.0');
        if (!preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.-]+)?$/', $version)) throw new InvalidArgumentException('Version must follow semantic versioning.');
        $runtimeKey = isset($d['runtime_key']) ? trim((string)$d['runtime_key']) : null;
        if ($runtimeKey !== null && $runtimeKey !== '' && !preg_match('/^[a-z][a-z0-9_.-]{1,99}$/', $runtimeKey)) throw new InvalidArgumentException('Invalid runtime handler key.');
        return [
            'slug' => $slug, 'name_bn' => $nameBn, 'name_en' => $nameEn,
            'description_bn' => trim((string)($d['description_bn'] ?? '')),
            'description_en' => trim((string)($d['description_en'] ?? '')),
            'category_slug' => trim((string)($d['category_slug'] ?? '')),
            'execution_type' => $execution, 'version' => $version,
            'visibility' => in_array(($d['visibility'] ?? 'public'), ['public','private'], true) ? (string)($d['visibility'] ?? 'public') : 'public',
            'runtime_key' => $runtimeKey ?: null,
            'input_schema' => is_array($d['input_schema'] ?? null) ? $d['input_schema'] : new \stdClass(),
            'output_schema' => is_array($d['output_schema'] ?? null) ? $d['output_schema'] : new \stdClass(),
            'config' => is_array($d['config'] ?? null) ? $d['config'] : new \stdClass(),
        ];
    }

    private function audit(PDO $db, int $actor, string $action, int $toolId, array $metadata): void
    {
        $stmt = $db->prepare('INSERT INTO admin_audit_logs (actor_user_id,action,resource_type,resource_id,metadata) VALUES (:actor,:action,\'tool\',:resource,CAST(:metadata AS JSONB))');
        $stmt->execute(['actor' => $actor, 'action' => $action, 'resource' => (string)$toolId, 'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR)]);
    }
}
