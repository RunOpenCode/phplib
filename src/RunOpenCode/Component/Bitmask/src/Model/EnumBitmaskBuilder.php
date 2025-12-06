<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Model;

use RunOpenCode\Component\Bitmask\Exception\InvalidArgumentException;

/**
 * @template T of \BackedEnum
 */
final class EnumBitmaskBuilder
{
    private Bitmask $mask;

    /**
     * @param class-string<T> $enum
     */
    public function __construct(private readonly string $enum)
    {
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

        $this->mask = Bitmask::zeroes((int)\ceil($bits / 8));
    }

    /**
     * Get built bitmask.
     */
    public function get(): Bitmask
    {
        return Bitmask::binary($this->mask->toBinary());
    }

    /**
     * @param T ...$case
     *
     * @return $this
     */
    public function add(\BackedEnum ...$case): self
    {
        if (0 === \count($case)) {
            throw new InvalidArgumentException('Expected at least one case to add to bitmask.');
        }

        foreach ($case as $current) {
            if ($current::class !== $this->enum) {
                throw InvalidArgumentException::type($this->enum, $current);
            }

            \assert(\is_int($current->value));

            $this->mask = $this->mask->true($current->value);
        }

        return $this;
    }

    /**
     * @param T ...$case
     *
     * @return $this
     */
    public function remove(\BackedEnum ...$case): self
    {
        if (0 === \count($case)) {
            throw new InvalidArgumentException('Expected at least one case to add to bitmask.');
        }

        foreach ($case as $current) {
            if ($current::class !== $this->enum) {
                throw InvalidArgumentException::type($this->enum, $current);
            }

            \assert(\is_int($current->value));

            $this->mask = $this->mask->false($current->value);
        }

        return $this;
    }
}
