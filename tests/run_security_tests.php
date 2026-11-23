<?php
/**
 * Test Runner for Men Let's Talk Security & Compliance Suite.
 */

declare(strict_types=1);

require_once __DIR__ . '/test_security.php';

global $SECURITY_TESTS;
$total = count($SECURITY_TESTS);
$passed = 0;
$failed = 0;

echo "Running {$total} Men Let's Talk Security & Data Protection Tests...\n\n";

foreach ($SECURITY_TESTS as $name => $test_fn) {
    try {
        $test_fn();
        echo "  [PASS] {$name}\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "  [FAIL] {$name}: {$e->getMessage()}\n";
        $failed++;
    }
}

echo "\nSummary: {$passed} passed, {$failed} failed, {$total} total.\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
