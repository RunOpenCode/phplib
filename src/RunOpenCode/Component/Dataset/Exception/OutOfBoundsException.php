<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Dataset\Exception;

class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
    public function __construct(
        public readonly mixed $key,
        public readonly mixed $value,
        ?string               $message = null,
        ?\Throwable           $previous = null
    ) {
        $message = $message ?? \sprintf(
            'Key "%s" for value "%s" is out of bounds.',
            \var_export($key, true),
            \var_export($value, true)
        );

        parent::__construct($message, 0, $previous);
    }
}
