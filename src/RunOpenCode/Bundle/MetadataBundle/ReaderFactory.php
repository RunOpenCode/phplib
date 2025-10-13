<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\MetadataBundle;

use Psr\Cache\CacheItemPoolInterface;
use RunOpenCode\Component\Metadata\Contract\ReaderInterface;
use RunOpenCode\Component\Metadata\Reader;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;

final readonly class ReaderFactory
{
    private function __construct()
    {
        // noop.
    }

    public static function create(CacheItemPoolInterface $pool, string $environment): ReaderInterface
    {
        $memory = new ArrayAdapter(storeSerialized: false);
        $stack  = 'prod' === $environment ? new ChainAdapter([$memory, $pool]) : $memory;

        return new Reader($stack);
    }
}
