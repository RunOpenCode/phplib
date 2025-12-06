<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Dbal\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use RunOpenCode\Component\Bitmask\Exception\InvalidArgumentException;
use RunOpenCode\Component\Bitmask\Model\Bitmask;

/**
 * Prototype for implementing your own bitmask storage for collection of int backed enums.
 *
 * Each enum value needs to be non-negative int denoting bit position. Type will determine
 * number of bits required to store enum array.
 *
 * @template T of \BackedEnum
 */
abstract class AbstractEnumBitmaskType extends Type
{
    private BitmaskType $inner;

    public function __construct(?BitmaskType $inner = null)
    {
        $this->inner = $inner ?? new BitmaskType();
    }

    /**
     * Convert list of enumeration values to bitmask.
     *
     * @param list<T>|null|mixed $value List of enumeration values to store into database.
     *
     * @return ($value is null ? null : non-empty-string) Binary encoded string.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        \assert(\is_array($value), InvalidArgumentException::type('array', $value));

        $bitmask = Bitmask::zeroes($this->length());

        /**
         * @var T $case
         */
        foreach ($value as $case) {
            // @phpstan-ignore-next-line
            $bitmask = $bitmask->true($case->value);
        }

        return $this->inner->convertToDatabaseValue($bitmask, $platform);
    }

    /**
     * Convert bitmask to list of enumeration values.
     *
     * @param non-empty-string|null $value Binary encoded string.
     *
     * @return ($value is null ? null : list<T>) List of enumeration values.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if (null === $value) {
            return null;
        }

        /** @var Bitmask $bitmask */
        $bitmask = $this->inner->convertToPHPValue($value, $platform);
        $enum    = $this->getEnum();
        $result  = [];

        foreach ($bitmask as $position => $bit) {
            if (!$bit) {
                continue;
            }

            $result[] = $enum::from($position);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        \assert(!isset($column['length']), new InvalidArgumentException('Length will be determined automatically based on the enum values.'));

        $column['length'] = (int)\ceil($this->length() / 8);

        return $this->inner->getSQLDeclaration($column, $platform);
    }

    /**
     * Get type name.
     *
     * @return non-empty-string Type name.
     */
    abstract public function getName(): string;

    /**
     * Get underlying enum.
     *
     * @return class-string<T>
     */
    abstract protected function getEnum(): string;

    /**
     * Calculate required length based on underlying enum values.
     *
     * @return int Number of bytes to allocate.
     */
    private function length(): int
    {
        $enum  = $this->getEnum();
        $cases = $enum::cases();
        $max   = 0;

        /**
         * @var T $case
         */
        foreach ($cases as $case) {
            \assert(\is_int($case->value) && $case->value >= 0, new InvalidArgumentException(\sprintf(
                'Enum cases can use only non-negative integer values, case "%s::%s" uses "%s".',
                $enum,
                $case->name,
                $case->value
            )));

            $max = \max($max, $case->value);
        }

        /** @var non-negative-int $max */
        $bits = $max === 0 ? 1 : (int)\ceil(\log($max + 1, 2));

        return (int)\ceil($bits / 8) * 8;
    }
}
