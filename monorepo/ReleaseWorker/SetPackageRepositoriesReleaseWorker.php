<?php

declare(strict_types=1);

namespace Monorepo\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Exception\MissingComposerJsonException;
use Symplify\SmartFileSystem\SmartFileInfo;

final readonly class SetPackageRepositoriesReleaseWorker implements ReleaseWorkerInterface
{
    public function __construct(
        private ComposerJsonProvider $composerJsonProvider,
        private JsonFileManager      $jsonFileManager
    ) {
    }

    public function work(Version $version): void
    {
        $packageJsons = $this->composerJsonProvider->getPackageComposerJsons();
        $repositories = [];

        foreach ($packageJsons as $packageJson) {
            $packageFileInfo = $packageJson->getFileInfo() instanceof SmartFileInfo ? $packageJson->getFileInfo() : throw new MissingComposerJsonException();
            $relativePath    = \str_replace('src/RunOpenCode', '../..', $packageFileInfo->getRelativeDirectoryPath());

            $repositories[$packageJson->getName()] = [
                'type'    => 'path',
                'url'     => $relativePath,
                'options' => [
                    'symlink' => false,
                ],
            ];
        }


        foreach ($packageJsons as $packageJson) {
            $packageJson->setRepositories(\array_values(
                \array_filter($repositories, static function(string $packageName) use ($packageJson): bool {
                    return $packageJson->getName() !== $packageName;
                }, \ARRAY_FILTER_USE_KEY)
            ));
            $packageFileInfo = $packageJson->getFileInfo() instanceof SmartFileInfo ? $packageJson->getFileInfo() : throw new MissingComposerJsonException();

            $this->jsonFileManager->printJsonToFileInfo($packageJson->getJsonArray(), $packageFileInfo);
        }
    }

    public function getDescription(Version $version): string
    {
        return 'Set "repositories" in "composer.json" of each individual package to point to local origin.';
    }
}
