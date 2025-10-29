<?php

declare(strict_types=1);

namespace Monorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symfony\Component\Process\Process;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final readonly class BranchValidatorWorker implements ReleaseWorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDescription(Version $version): string
    {
        return 'Verify that release is executed against "master" branch';
    }

    public function work(Version $version): void
    {
        $process = new Process([
            '/usr/bin/git',
            'symbolic-ref',
            '--short',
            '-q',
            'HEAD',
        ])->mustRun();

        $branch = \trim($process->getOutput());

        if ('master' !== $branch) {
            throw new \RuntimeException(\sprintf(
                'Release must be executed from "master" branch, you are currently on "%s".',
                $branch
            ));
        }
    }
}
