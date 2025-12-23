<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Exception;

class StreamOverflowException extends ExpectationFailedException
{
    public function __construct(int $capacity, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf(
            'Defined capacity of %d items exceeded.',
            $capacity
        ), $previous);
    }
}
