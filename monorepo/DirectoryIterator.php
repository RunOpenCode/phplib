<?php

declare(strict_types=1);

namespace Monorepo;

/**
 * @implements \IteratorAggregate<string, string>
 */
final readonly class DirectoryIterator implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        $iterator    = new LibraryIterator();
        $projectRoot = \dirname(__DIR__);

        foreach ($iterator as $directory => $repository) {
            $absolutePath     = \sprintf('%s/%s', $projectRoot, $directory);
            $composerJsonFile = \sprintf('%s/composer.json', $absolutePath);
            // @phpstan-ignore-next-line
            $libraryName      = \json_decode(\file_get_contents($composerJsonFile), true)['name'];

            /** @var string $libraryName */
            yield $absolutePath => $libraryName;
        }
    }
}
