<?php

declare(strict_types=1);

namespace Monorepo;

use Symfony\Component\Finder\Finder;
use function Symfony\Component\String\u;

/**
 * Iterate libraries in monorepo.
 *
 * @implements \IteratorAggregate<string, string> List of library directory paths with repository URLs.
 */
final readonly class LibraryIterator implements \IteratorAggregate
{
    public const array DIRECTORIES = [
        'src/RunOpenCode/Component',
        'src/RunOpenCode/Bundle',
    ];

    public static function create(): self
    {
        return new self();
    }

    public function getIterator(): \Traversable
    {
        $projectRoot = \dirname(__DIR__) ?: throw new \RuntimeException('Could not determine project root directory.');
        $projectRoot = \rtrim($projectRoot, DIRECTORY_SEPARATOR);

        foreach (self::DIRECTORIES as $directory) {
            $packagesDirectories = Finder::create()
                                         ->in(\sprintf('%s/%s', $projectRoot, $directory))
                                         ->depth(0)
                                         ->directories();

            foreach ($packagesDirectories as $packageDirectory) {
                $library    = u($packageDirectory->getBasename())->kebab()->toString();
                $repository = \sprintf('git@github.com:RunOpenCode/%s.git', $library);
                
                yield \sprintf('%s/%s', $directory, $packageDirectory->getBasename()) => $repository;
            }
        }
    }
}
