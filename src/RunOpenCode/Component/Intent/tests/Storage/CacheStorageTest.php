<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Tests\Storage;

use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Storage\CacheStorage;
use RunOpenCode\Component\Intent\Tests\AbstractIntentStorageTestBase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CacheStorageTest extends AbstractIntentStorageTestBase
{
    protected function getIntentStorage(): IntentStorageInterface
    {
        return new CacheStorage(new ArrayAdapter());
    }
}
