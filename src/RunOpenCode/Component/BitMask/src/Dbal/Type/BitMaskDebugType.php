<?php

declare(strict_types=1);

namespace RunOpenCode\Component\BitMask\Dbal\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use RunOpenCode\Component\BitMask\Exception\InvalidArgumentException;
use RunOpenCode\Component\BitMask\Model\BitMask;

/**
 * Bitmask which uses string representation for storing bits.
 *
 * String representation is string which consist of zeroes and one, example: "101001".
 *
 * If length is not provided, string type declaration will be used, otherwise, based
 * on given length, either string type declaration or clob type declaration will be
 * used.
 *
 * This type is suitable for storing bitmask in database for traceability and debugging
 * purposes, not for executing queries and indexing.
 */
final class BitMaskDebugType extends Type
{
    public const string NAME = 'bitmask_debug';

    /**
     * {@inheritdoc}
     *
     * @return ($value is null ? null : non-empty-string)
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        \assert($value instanceof BitMask, InvalidArgumentException::type(BitMask::class, $value));

        return $value->toString();
    }

    /**
     * {@inheritdoc}
     *
     * @return ($value is null ? null : BitMask)
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?BitMask
    {
        if (null === $value) {
            return null;
        }

        \assert(\is_string($value), InvalidArgumentException::type('string', $value));

        return BitMask::string($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        \assert(isset($column['length']) && \is_int($column['length']), new InvalidArgumentException(\sprintf(
            'Length must be provided when declaring column of type "%s".',
            self::NAME
        )));

        // Calculated actual length for string representation.
        $column['length'] *= 8;

        if ($column['length'] > 255) {
            return $platform->getClobTypeDeclarationSQL($column);
        }

        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
