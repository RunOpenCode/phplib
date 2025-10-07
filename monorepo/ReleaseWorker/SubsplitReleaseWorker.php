<?php

declare(strict_types=1);

namespace Monorepo\ReleaseWorker;

use Monorepo\LibraryIterator;
use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Process\ProcessRunner;

final readonly class SubsplitReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(private ProcessRunner $runner)
    {
        // noop.
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(Version $version): string
    {
        return \sprintf(
            'Subsplit project to readonly repositories using version: "%s".',
            $version->getVersionString(),
        );
    }
    
    public function work(Version $version): void
    {
        $iterator = LibraryIterator::create();
        $projectRoot = \dirname(__DIR__, 2);
        
        foreach ($iterator as $directory => $repository) {
            $command = \sprintf(
                'git --git-dir=%s/.git subsplit publish %s:%s --update --heads=master --tags="%s"',
                $projectRoot,
                $directory,
                $repository,
                $version->getVersionString()
            );

            $this->runner->run($command);
        }

        $this->runner->run('git checkout master');
    }
}
