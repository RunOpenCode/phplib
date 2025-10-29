<?php

declare(strict_types=1);

namespace Monorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Exception\MissingComposerJsonException;
use Symplify\SmartFileSystem\SmartFileInfo;

final readonly class RemovePackageRepositoriesReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private ComposerJsonProvider $composerJsonProvider,
        private JsonFileManager      $jsonFileManager
    ) {
    }

    public function work(Version $version): void
    {
        $packageJsons = $this->composerJsonProvider->getPackageComposerJsons();

        foreach ($packageJsons as $packageJson) {
            $packageJson->setRepositories([]);
            $packageFileInfo = $packageJson->getFileInfo();

            if (!$packageFileInfo instanceof SmartFileInfo) {
                throw new MissingComposerJsonException();
            }

            $this->jsonFileManager->printJsonToFileInfo($packageJson->getJsonArray(), $packageFileInfo);
        }
    }

    public function getDescription(Version $version): string
    {
        return 'Remove "repositories" from "composer.json" of each individual package.';
    }
}
