<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\PHPUnit;

trait AssertMetadataTrait
{
    public function assertMetadataEquals(mixed $expected, mixed $actual): void
    {
        self::assertThat($actual, new Constraint\MetadataEqualsConstraint($expected));
    }
}
