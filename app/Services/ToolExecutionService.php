<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;
use Throwable;

final class ToolExecutionService
{
    public function __construct(
        private readonly ToolRuntime $runtime,
        private readonly ?PDO $db = null
    ) {}

    private function connection(): PDO { return $this->db ?? Database::connection(); }

    public function execute(string $slug, array $input, ?int $userId = null): mixed
    {
        $db = $this->connection();
        $lookup = $db->prepare("SELECT id,execution_type,runtime_key,input_schema FROM tools WHERE slug=:slug AND status='published' AND is_active=TRUE AND visibility='public' LIMIT 1");
        $lookup->execute(['slug' => $slug]);
        $tool = $lookup->fetch();
        if (!$tool) throw new RuntimeException('Tool is unavailable.');
        if ($tool['execution_type'] !== 'server' || empty($tool['runtime_key'])) {
            throw new RuntimeException('This tool does not use a server runtime.');
        }

        $schema = is_array($tool['input_schema']) ? $tool['input_schema'] : json_decode((string)$tool['input_schema'], true);
        $schema = is_array($schema) ? $schema : [];
        (new ToolInputValidator())->validate($input, $schema);

        $start = hrtime(true);
        $run = $db->prepare("INSERT INTO tool_executions (tool_id,user_id,status) VALUES (:tool_id,:user_id,'running') RETURNING id");
        $run->execute(['tool_id' => (int)$tool['id'], 'user_id' => $userId]);
        $runId = (int)$run->fetchColumn();

        try {
            $result = $this->runtime->execute((string)$tool['runtime_key'], $input, $schema);
            $duration = (int)((hrtime(true) - $start) / 1_000_000);
            $done = $db->prepare("UPDATE tool_executions SET status='succeeded',duration_ms=:duration,finished_at=NOW() WHERE id=:id");
            $done->execute(['duration' => $duration, 'id' => $runId]);
            return $result;
        } catch (Throwable $e) {
            $duration = (int)((hrtime(true) - $start) / 1_000_000);
            $failed = $db->prepare("UPDATE tool_executions SET status='failed',duration_ms=:duration,error_code=:error_code,finished_at=NOW() WHERE id=:id");
            $failed->execute(['duration' => $duration, 'error_code' => 'HANDLER_FAILED', 'id' => $runId]);
            error_log('ToolkitPro tool execution failed (' . $slug . '): ' . $e->getMessage());
            throw new RuntimeException('Tool execution failed. Please try again later.', 0, $e);
        }
    }
}
