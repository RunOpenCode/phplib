<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Exception;

class StreamEmptyException extends ExpectationFailedException
{
    public function __construct(?string $message = null, ?\Throwable $previous = null)
    {
        parent::__construct($message ?? 'Stream is empty.', $previous);
    }
}
