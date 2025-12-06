<?php

class BitSet
{

    public function __construct(int $value = 0)
    {
    }

    public function andOp(BitSet $set): void
    {
    }

    public function andNotOp(BitSet $set): void
    {
    }

    public function cardinality(): int
    {
    }

    public function clear(int $from = -1, int $to = 0): void
    {
    }

    /**
     * @param list<non-negative-int> $arr
     *
     * @return BitSet
     */
    static public function fromArray(array $arr): BitSet
    {
    }

    static public function fromInteger(int $value): BitSet
    {
    }

    static public function fromString(string $str): BitSet
    {
    }

    static public function fromRawValue(string $str): BitSet
    {
    }

    public function get(int $index): bool
    {
    }

    /**
     * @return non-empty-string
     */
    public function getRawValue(): string
    {
    }

    public function intersects(BitSet $set): bool
    {
    }

    public function isEmpty(): bool
    {
    }

    public function length(): int
    {
    }

    public function nextClearBit(int $start): bool|int
    {
    }

    public function nextSetBit(int $start): bool|int
    {
    }

    public function orOp(BitSet $set): void
    {
    }

    public function previousClearBit(int $start): bool|int
    {
    }

    public function previousSetBit(int $start): bool|int
    {
    }

    public function set(int $from = -1, int $to = 0): void
    {
    }

    /**
     * @return non-negative-int
     */
    public function size(): int
    {
    }

    /**
     * @return list<non-negative-int>
     */
    public function toArray(): array
    {
    }

    public function toInteger(): int
    {
    }

    public function xorOp(BitSet $set): void
    {
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
    }
}
