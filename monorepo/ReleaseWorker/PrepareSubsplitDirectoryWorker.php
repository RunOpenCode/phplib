<?php

declare(strict_types=1);

namespace Monorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symfony\Component\Process\Process;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

final readonly class PrepareSubsplitDirectoryWorker implements ReleaseWorkerInterface
{
    private const string SUBSPLIT_DIR = '%s/.subsplit';

    /**
     * {@inheritdoc}
     */
    public function getDescription(Version $version): string
    {
        return 'Create blank subsplit directory.';
    }

    public function work(Version $version): void
    {
        $projectRoot = \dirname(__DIR__, 2);
        $subsplitDir = \sprintf(self::SUBSPLIT_DIR, $projectRoot);

        if (\is_dir($subsplitDir)) {
            $process = new Process(['rm', '-rf', $subsplitDir]);
            $result  = $process->run();

            if (0 !== $result) {
                throw new \RuntimeException(\sprintf(
                    'Could not remove existing subsplit directory "%s". Error: %s',
                    $subsplitDir,
                    $process->getErrorOutput()
                ));
            }
        }

        $process = new Process(['mkdir', $subsplitDir]);
        $result  = $process->run();

        if (0 !== $result) {
            throw new \RuntimeException(\sprintf(
                'Could not create subsplit directory "%s". Error: %s',
                $subsplitDir,
                $process->getErrorOutput()
            ));
        }
    }
}