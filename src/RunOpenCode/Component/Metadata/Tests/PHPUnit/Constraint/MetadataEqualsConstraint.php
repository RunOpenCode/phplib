<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

final class MetadataEqualsConstraint extends Constraint
{
    public function __construct(
        private readonly mixed $value
    ) {
    }

    public function toString(): string
    {
        return \sprintf(
            'is equal to %s',
            Exporter::export($this->value)
        );
    }

    #[\Override]
    protected function matches(mixed $other): bool
    {
        return \json_encode(
            self::dump($other),
            JSON_THROW_ON_ERROR
        ) === \json_encode(
            self::dump($this->value),
            JSON_THROW_ON_ERROR
        );
    }

    private static function dump(mixed $value): mixed
    {
        if (\is_scalar($value)) {
            return $value;
        }

        if (\is_array($value)) {
            \ksort($value);

            return \array_map(static fn(mixed $value): mixed => self::dump($value), $value);
        }

        if (\is_object($value)) {
            $data              = \get_object_vars($value);
            $data['__class__'] = $value::class;

            \ksort($data);

            return \array_map(static fn(mixed $value): mixed => self::dump($value), $data);
        }

        return $value;
    }
}
