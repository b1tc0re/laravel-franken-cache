<?php

declare(strict_types=1);

namespace b1tc0re\LaravelFrankenCache\Test\Support;

use LogicException;

final class TieredCacheFake
{
    /**
     * @var array<string, string>
     */
    public static array $values = [];

    /**
     * @var list<array{method: string, arguments: list<mixed>}>
     */
    public static array $calls = [];

    public static function reset(): void
    {
        self::$values = [];
        self::$calls  = [];
    }

    /**
     * @return array{method: string, arguments: list<mixed>}
     */
    public static function lastCall(string $method): array
    {
        foreach (array_reverse(self::$calls) as $call) {
            if ($call['method'] === $method) {
                return $call;
            }
        }

        throw new LogicException("No call recorded for {$method}.");
    }

    public static function record(string $method, mixed ...$arguments): void
    {
        self::$calls[] = [
            'method'    => $method,
            'arguments' => $arguments,
        ];
    }

    public static function recordFunction(string $function, mixed ...$arguments): void
    {
        $separatorPosition = strrpos($function, '\\');
        $method            = $separatorPosition === false
            ? $function
            : substr($function, $separatorPosition + 1);

        self::record($method, ...$arguments);
    }
}
