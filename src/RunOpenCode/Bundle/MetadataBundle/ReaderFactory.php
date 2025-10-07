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
    
    public static function create(CacheItemPoolInterface $pool): ReaderInterface
    {
        $memory = new ArrayAdapter(storeSerialized: false);
        $stack  = new ChainAdapter([$memory, $pool]);
        
        return new Reader($stack);
    }
}