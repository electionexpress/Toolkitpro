<?php
declare(strict_types=1);
namespace App\Services;
final class ToolRegistry
{
    private array $tools = [];
    public function register(string $slug, array $definition): void
    {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) throw new \InvalidArgumentException('Invalid tool slug.');
        $this->tools[$slug] = $definition + ['slug' => $slug];
    }
    public function all(): array { return array_values($this->tools); }
    public function find(string $slug): ?array { return $this->tools[$slug] ?? null; }
}