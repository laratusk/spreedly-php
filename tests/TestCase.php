<?php

declare(strict_types=1);

namespace Laratusk\Spreedly\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Load a JSON fixture file.
     *
     * @return array<string, mixed>
     */
    protected function loadFixture(string $path): array
    {
        $fullPath = __DIR__.'/Fixtures/'.ltrim($path, '/');

        if (! file_exists($fullPath)) {
            throw new RuntimeException("Fixture file not found: {$fullPath}");
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw new RuntimeException("Failed to read fixture file: {$fullPath}");
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new RuntimeException("Failed to decode fixture JSON: {$fullPath}");
        }

        return $decoded;
    }
}
