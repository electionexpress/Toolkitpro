<?php
declare(strict_types=1);
namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

/**
 * Executes only trusted, explicitly registered handlers.
 * Tool definitions from the database can never supply PHP code or eval payloads.
 */
final class ToolRuntime
{
    /** @var array<string, callable> */
    private array $handlers = [];

    public function register(string $key, callable $handler): void
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]{1,99}$/', $key)) {
            throw new InvalidArgumentException('Invalid runtime handler key.');
        }
        $this->handlers[$key] = $handler;
    }

    public function execute(string $key, array $input, array $schema = []): mixed
    {
        $handler = $this->handlers[$key] ?? null;
        if ($handler === null) {
            throw new RuntimeException('Tool runtime handler is not installed.');
        }
        if ($schema !== []) {
            (new ToolInputValidator())->validate($input, $schema);
        }
        return $handler($input);
    }

    public function has(string $key): bool
    {
        return isset($this->handlers[$key]);
    }
}
