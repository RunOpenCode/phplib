<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Logger\Contract;

/**
 * Logger context.
 *
 * Provides additional context data to be appended to the existing context passed to the logger methods.
 *
 * @template T of mixed[] = mixed[]
 */
interface LoggerContextInterface
{
    /**
     * Get context data to append to the existing context.
     *
     * @param mixed[] $current Current context passed to the logger method.
     *
     * @return T Context data to append to the current context.
     */
    public function get(array $current): array;
}
