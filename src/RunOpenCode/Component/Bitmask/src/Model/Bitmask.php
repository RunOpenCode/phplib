<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Bitmask\Model;

use RunOpenCode\Component\Bitmask\Exception\InvalidArgumentException;
use RunOpenCode\Component\Bitmask\Exception\OutOfBoundsException;

/**
 * @implements \IteratorAggregate<non-negative-int, bool>
 */
final readonly class Bitmask implements \IteratorAggregate, \Stringable, \Countable
{
    private \BitSet $bitset;

    private function __construct(\BitSet $bitset)
    {
        $this->bitset = $bitset;
    }

    /**
     * Create bitmask with all zeros.
     */
    public static function zeroes(int $length): self
    {
        if ($length % 8 !== 0) {
            throw new InvalidArgumentException(\sprintf(
                'Length must be dividable by 8, %d provided.',
                $length
            ));
        }

        return new self(new \BitSet($length));
    }

    /**
     * Create bitmask from string representation.
     */
    public static function string(string $value, bool $appendZeros = false): self
    {
        \assert(\Safe\preg_match('/^[01]+$/', $value) === 1, new InvalidArgumentException(\sprintf(
            'The value must contain zeroes and ones only, "%s" provided.',
            $value
        )));

        if (!$appendZeros && \strlen($value) % 8 !== 0) {
            throw new InvalidArgumentException(\sprintf(
                'String length must be dividable by 8, %d characters provided.',
                \strlen($value),
            ));
        }

        return new self(\BitSet::fromString($value));
    }

    /**
     * Create bitmask from a binary encoded string.
     */
    public static function binary(string $value): self
    {
        return new self(\BitSet::fromRawValue($value));
    }

    /**
     * Check if all bits are zeros.
     */
    public function empty(): bool
    {
        return $this->bitset->isEmpty();
    }

    /**
     * Get the number of true bits.
     */
    public function cardinality(): int
    {
        return $this->bitset->cardinality();
    }

    /**
     * Set bit value to 1 on given position.
     *
     * @param int $position Position of the bit which value is being updated.
     */
    public function true(int $position): Bitmask
    {
        if ($position >= $this->bitset->size()) {
            throw new OutOfBoundsException(\sprintf(
                'Size of the bitmask is %d, position %d is out of bounds.',
                $this->bitset->size(),
                $position,
            ));
        }

        $bitset = \BitSet::fromRawValue($this->bitset->getRawValue());

        $bitset->set($position, $position);

        return new self($bitset);
    }

    /**
     * Set bit value to 0 on given position.
     *
     * @param int $position Position of the bit which value is being updated.
     */
    public function false(int $position): Bitmask
    {
        if ($position >= $this->bitset->size()) {
            throw new OutOfBoundsException(\sprintf(
                'Size of the bitmask is %d, position %d is out of bounds.',
                $this->bitset->size(),
                $position,
            ));
        }

        $bitset = \BitSet::fromRawValue($this->bitset->getRawValue());

        $bitset->clear($position, $position);

        return new self($bitset);
    }

    /**
     * Set bit value to 0 or 1 on given position.
     *
     * @param int  $position Position of the bit which value is being updated.
     * @param bool $value    TRUE for 1, FALSE for 0
     */
    public function set(int $position, bool $value): Bitmask
    {
        if (true === $value) {
            return $this->true($position);
        }

        return $this->false($position);
    }

    /**
     * Get bit value on given position.
     *
     * @param int $position Position of the bit which value is fetched.
     *
     * @return bool
     */
    public function get(int $position): bool
    {
        if ($position >= $this->bitset->size()) {
            throw new OutOfBoundsException(\sprintf(
                'Size of the bitmask is %d, position %d is out of bounds.',
                $this->bitset->size(),
                $position,
            ));
        }

        return $this->bitset->get($position);
    }

    public function and(Bitmask $mask): self
    {
        if ($mask->count() !== $this->count()) {
            throw new InvalidArgumentException(\sprintf(
                'Operator "and" may be applied on bitmasks of same size, %d provided.',
                \count($mask)
            ));
        }

        $bitset = \BitSet::fromRawValue($this->bitset->getRawValue());

        $bitset->andOp(\BitSet::fromRawValue($mask->toBinary()));

        return new self($bitset);
    }

    public function andNot(Bitmask $mask): self
    {
        if ($mask->count() !== $this->count()) {
            throw new InvalidArgumentException(\sprintf(
                'Operator "and not" may be applied on bitmasks of same size, %d provided.',
                \count($mask)
            ));
        }

        $bitset = \BitSet::fromRawValue($this->bitset->getRawValue());

        $bitset->andNotOp(\BitSet::fromRawValue($mask->toBinary()));

        return new self($bitset);
    }

    public function or(Bitmask $mask): self
    {
        if ($mask->count() !== $this->count()) {
            throw new InvalidArgumentException(\sprintf(
                'Operator "or" may be applied on bitmasks of same size, %d provided.',
                \count($mask)
            ));
        }

        $bitset = \BitSet::fromRawValue($this->bitset->getRawValue());

        $bitset->orOp(\BitSet::fromRawValue($mask->toBinary()));

        return new self($bitset);
    }

    public function xor(Bitmask $mask): self
    {
        if ($mask->count() !== $this->count()) {
            throw new InvalidArgumentException(\sprintf(
                'Operator "xor" may be applied on bitmasks of same size, %d provided.',
                \count($mask)
            ));
        }

        $bitset = \BitSet::fromRawValue($this->bitset->getRawValue());

        $bitset->xorOp(\BitSet::fromRawValue($mask->toBinary()));

        return new self($bitset);
    }

    public function equals(Bitmask $mask): bool
    {
        if ($mask->count() !== $this->count()) {
            return false;
        }

        return $this->andNot($mask)->empty();
    }

    /**
     * Get binary encoded string of bitmask.
     *
     * @return non-empty-string
     */
    public function toBinary(): string
    {
        return $this->bitset->getRawValue();
    }

    /**
     * Get string representation of bitmask.
     *
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->bitset->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        $size = $this->bitset->size();

        for ($i = 0; $i < $size; ++$i) {
            yield $i => $this->bitset->get($i);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->bitset->size();
    }

    /**
     * {@inheritdoc}
     *
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->bitset->__toString();
    }
}
