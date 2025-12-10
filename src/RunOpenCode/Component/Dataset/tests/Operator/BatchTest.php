<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Tests\Operator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function RunOpenCode\Component\Dataset\batch;

final class BatchTest extends TestCase
{
    #[Test]
    public function process_batches(): void
    {
        $operator = batch([
            'a' => 2,
            'b' => 10,
            'c' => 5,
            'd' => 1,
        ], static function(iterable $batch, int $batchNumber): iterable {
            foreach ($batch as [$key, $value]) {
                yield \sprintf('processed_%s', $key) => $batchNumber * $value;
            }
        }, 2);

        $this->assertSame([
            'processed_a' => 2,
            'processed_b' => 10,
            'processed_c' => 10,
            'processed_d' => 2,
        ], \iterator_to_array($operator));
    }
}
