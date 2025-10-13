<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Contract;

/**
 * Iterable stream operator.
 *
 * @template TKey
 * @template TValue
 *
 * @extends StreamInterface<TKey, TValue>
 */
interface OperatorInterface extends StreamInterface
{
}
