<?php

declare(strict_types=1);

namespace Monorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Exception\MissingComposerJsonException;
use Symplify\MonorepoBuilder\Utils\VersionUtils;
use Symplify\SmartFileSystem\SmartFileInfo;

final readonly class SetNextPackageVersionReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private ComposerJsonProvider $composerJsonProvider,
        private JsonFileManager      $jsonFileManager,
        private VersionUtils         $versionUtils,
    ) {
    }

    public function work(Version $version): void
    {
        $packageJsons = $this->composerJsonProvider->getPackageComposerJsons();
        $nextVersion  = \ltrim($this->versionUtils->getRequiredNextFormat($version), '^~');

        foreach ($packageJsons as $packageJson) {
            $packageJson->setVersion($nextVersion);
            $packageFileInfo = $packageJson->getFileInfo();

            if (!$packageFileInfo instanceof SmartFileInfo) {
                throw new MissingComposerJsonException();
            }

            $this->jsonFileManager->printJsonToFileInfo($packageJson->getJsonArray(), $packageFileInfo);
        }
    }

    public function getDescription(Version $version): string
    {
        return 'Update "version" in "composer.json" to next version of each individual package.';
    }
}