<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Services\ToolInputValidator;
use App\Services\ToolRuntime;

$passed = 0;
$failed = 0;

$test = static function (string $name, callable $run) use (&$passed, &$failed): void {
    try {
        $run();
        $passed++;
        echo "[PASS] {$name}\n";
    } catch (Throwable $e) {
        $failed++;
        fwrite(STDERR, "[FAIL] {$name}: {$e->getMessage()}\n");
    }
};

$expectException = static function (string $class, callable $run): void {
    try {
        $run();
    } catch (Throwable $e) {
        if ($e instanceof $class) return;
        throw new RuntimeException('Expected ' . $class . ', got ' . $e::class);
    }
    throw new RuntimeException('Expected exception ' . $class . ' was not thrown.');
};

$schema = [
    'type' => 'object',
    'required' => ['text'],
    'properties' => [
        'text' => ['type' => 'string'],
        'mode' => ['type' => 'string', 'enum' => ['upper', 'lower']],
    ],
];

$test('accepts valid tool input', static function () use ($schema): void {
    (new ToolInputValidator())->validate(['text' => 'hello', 'mode' => 'upper'], $schema);
});

$test('rejects missing required input', static function () use ($schema, $expectException): void {
    $expectException(InvalidArgumentException::class, static fn () => (new ToolInputValidator())->validate([], $schema));
});

$test('rejects invalid input type', static function () use ($schema, $expectException): void {
    $expectException(InvalidArgumentException::class, static fn () => (new ToolInputValidator())->validate(['text' => 123], $schema));
});

$test('rejects values outside enum', static function () use ($schema, $expectException): void {
    $expectException(InvalidArgumentException::class, static fn () => (new ToolInputValidator())->validate(['text' => 'hello', 'mode' => 'execute'], $schema));
});

$test('executes only an explicitly registered handler', static function () use ($schema): void {
    $runtime = new ToolRuntime();
    $runtime->register('test.echo', static fn (array $input): array => ['text' => $input['text']]);
    $result = $runtime->execute('test.echo', ['text' => 'ToolkitPro'], $schema);
    if (($result['text'] ?? null) !== 'ToolkitPro') throw new RuntimeException('Unexpected runtime result.');
});

$test('rejects unregistered runtime handlers', static function () use ($expectException): void {
    $expectException(RuntimeException::class, static fn () => (new ToolRuntime())->execute('test.missing', []));
});

echo "\nTests: {$passed} passed, {$failed} failed.\n";
exit($failed === 0 ? 0 : 1);
