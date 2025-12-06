<?php

declare(strict_types=1);

namespace RunOpenCode\Component\BitMask\Dbal\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use RunOpenCode\Component\BitMask\Exception\InvalidArgumentException;
use RunOpenCode\Component\BitMask\Model\BitMask;

/**
 * Bitmask which uses binary column type for storing bits.
 */
final class BitMaskType extends Type
{
    public const string NAME = 'bitmask';

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

        return $value->toBinary();
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

        \assert(\is_resource($value) || \is_string($value), InvalidArgumentException::type(['resource', 'string'], $value));

        return BitMask::binary(\is_string($value) ? $value : \Safe\stream_get_contents($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        \assert(isset($column['length']), new InvalidArgumentException(\sprintf(
            'Length must be provided when declaring column of type "%s".',
            self::NAME
        )));

        // "fixed" ensures that "binary" is used instead of "varbinary" for MySQL.
        return $platform->getBinaryTypeDeclarationSQL([
            ...$column,
            'fixed' => true,
        ]);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
